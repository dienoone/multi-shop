<?php

namespace App\Services;

use App\Models\Order;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createPaymentIntent(Order $order): array
    {
        $intent = PaymentIntent::create([
            'amount'   => (int) round($order->total * 100),
            'currency' => strtolower($order->currency),

            'metadata' => [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
                'tenant_id'    => $order->tenant_id,
            ],

            'automatic_payment_methods' => [
                'enabled' => true,
            ],
        ]);

        return [
            'payment_intent_id' => $intent->id,
            'client_secret'     => $intent->client_secret,
        ];
    }

    public function constructWebhookEvent(string $payload, string $signature): \Stripe\Event
    {
        try {
            return Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            throw new \Exception('Invalid webhook signature.');
        } catch (UnexpectedValueException $e) {
            throw new \Exception('Invalid webhook payload.');
        }
    }
}
