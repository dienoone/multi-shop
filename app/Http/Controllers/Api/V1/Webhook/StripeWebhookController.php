<?php

namespace App\Http\Controllers\Api\V1\Webhook;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        try {
            $event = $this->stripeService->constructWebhookEvent($payload, $signature);
        } catch (\Exception $e) {
            Log::warning('Stripe webhook signature failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded'       => $this->handlePaymentSucceeded($event->data->object),
            'payment_intent.payment_failed'  => $this->handlePaymentFailed($event->data->object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentSucceeded(object $paymentIntent): void
    {
        $order = $this->findOrder($paymentIntent->id);

        if (!$order) {
            return;
        }

        $order->update([
            'payment_status' => PaymentStatus::Paid->value,
            'status'         => OrderStatus::Confirmed->value,
        ]);

        Log::info('Payment succeeded', [
            'order_id'          => $order->id,
            'order_number'      => $order->order_number,
            'payment_intent_id' => $paymentIntent->id,
        ]);

        // TODO: Fire OrderPaid event here later for email notifications
    }

    private function handlePaymentFailed(object $paymentIntent): void
    {
        $order = $this->findOrder($paymentIntent->id);

        if (!$order) {
            return;
        }

        $order->update([
            'payment_status' => PaymentStatus::Failed->value,
        ]);

        Log::warning('Payment failed', [
            'order_id'          => $order->id,
            'order_number'      => $order->order_number,
            'payment_intent_id' => $paymentIntent->id,
            'failure_message'   => $paymentIntent->last_payment_error?->message,
        ]);
    }

    private function findOrder(string $paymentIntentId): ?Order
    {
        $order = Order::withoutTenant()
            ->where('payment_intent_id', $paymentIntentId)
            ->first();

        if (!$order) {
            Log::error('Webhook received for unknown payment intent', [
                'payment_intent_id' => $paymentIntentId,
            ]);
        }

        return $order;
    }
}
