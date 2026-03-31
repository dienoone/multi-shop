<?php

namespace App\Repositories;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function allForCustomer(User $user, array $filters = []): LengthAwarePaginator
    {
        return Order::query()
            ->with('items')
            ->where('user_id', $user->id)
            ->when(
                isset($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function allForTenant(array $filters = []): LengthAwarePaginator
    {
        return Order::query()
            ->with(['items', 'user'])
            ->when(
                isset($filters['status']),
                fn($q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['payment_status']),
                fn($q) => $q->where('payment_status', $filters['payment_status'])
            )
            ->when(
                isset($filters['search']),
                fn($q) => $q->where('order_number', 'like', "%{$filters['search']}%")
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Order
    {
        return Order::with(['items.product', 'user'])->find($id);
    }

    public function findByIdForCustomer(int $id, User $user): ?Order
    {
        return Order::with('items.product')
            ->where('user_id', $user->id)
            ->find($id);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh(['items.product', 'user']);
    }
}
