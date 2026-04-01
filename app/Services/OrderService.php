<?php

namespace App\Services;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Services\OrderServiceInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService implements OrderServiceInterface
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected CartRepositoryInterface  $cartRepository,
        protected StripeService            $stripeService,
    ) {}

    public function listForCustomer(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->allForCustomer($user, $filters);
    }

    public function listForTenant(array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->allForTenant($filters);
    }

    public function findForCustomer(int $id, User $user): Order
    {
        $order = $this->orderRepository->findByIdForCustomer($id, $user);

        throw_if(!$order, ModelNotFoundException::class, 'Order not found.');

        return $order;
    }

    public function findForTenant(int $id): Order
    {
        $order = $this->orderRepository->findById($id);

        throw_if(!$order, ModelNotFoundException::class, 'Order not found.');

        return $order;
    }

    public function placeOrder(User $user, array $data): Order
    {
        $cart = $this->cartRepository->findOrCreateCart($user);

        throw_if(
            $cart->items->isEmpty(),
            ValidationException::withMessages([
                'cart' => 'Your cart is empty.',
            ])
        );

        return DB::transaction(function () use ($user, $cart, $data) {
            $subtotal = $cart->items->sum(
                fn($item) => $item->unit_price * $item->quantity
            );

            $total = $subtotal
                + ($data['shipping_amount'] ?? 0)
                + ($data['tax_amount'] ?? 0);

            $order = $this->orderRepository->create([
                'user_id'          => $user->id,
                'order_number'     => $this->generateOrderNumber(),
                'status'           => OrderStatus::Pending,
                'subtotal'         => $subtotal,
                'discount_amount'  => 0,
                'shipping_amount'  => $data['shipping_amount'] ?? 0,
                'tax_amount'       => $data['tax_amount'] ?? 0,
                'total'            => $total,
                'currency'         => $data['currency'] ?? 'USD',
                'shipping_address' => $data['shipping_address'],
                'notes'            => $data['notes'] ?? null,
                'payment_status'   => 'unpaid',
            ]);

            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id'   => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_sku'  => $cartItem->product->sku,
                    'unit_price'   => $cartItem->unit_price,
                    'quantity'     => $cartItem->quantity,
                    'subtotal'     => $cartItem->unit_price * $cartItem->quantity,
                ]);
            }

            $this->cartRepository->clearCart($cart);

            $paymentData = $this->stripeService->createPaymentIntent($order);

            $this->orderRepository->update($order, [
                'payment_intent_id' => $paymentData['payment_intent_id'],
                'payment_status'    => 'pending',
            ]);

            $order->client_secret = $paymentData['client_secret'];

            return $order->load('items');
        });
    }

    public function cancelOrder(int $id, User $user): Order
    {
        $order = $this->findForCustomer($id, $user);

        throw_if(
            !$order->status->canBeCancelled(),
            ValidationException::withMessages([
                'status' => "This order cannot be cancelled. Current status: {$order->status->value}.",
            ])
        );

        return $this->orderRepository->update($order, [
            'status' => OrderStatus::Cancelled,
        ]);
    }

    public function updateStatus(int $id, OrderStatus $status): Order
    {
        $order = $this->findForTenant($id);

        throw_if(
            !$order->status->canTransitionTo($status),
            ValidationException::withMessages([
                'status' => "Cannot transition from [{$order->status->value}] to [{$status->value}].",
            ])
        );

        return $this->orderRepository->update($order, ['status' => $status]);
    }

    private function generateOrderNumber(): string
    {
        // Format: ORD-2024-00123
        $year   = now()->year;
        $latest = Order::withoutTenant()
            ->whereYear('created_at', $year)
            ->lockForUpdate()
            ->count();

        return sprintf('ORD-%s-%05d', $year, $latest + 1);
    }
}
