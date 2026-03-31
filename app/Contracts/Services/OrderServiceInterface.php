<?php

namespace App\Contracts\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderServiceInterface
{
    public function listForCustomer(User $user, array $filters = []): LengthAwarePaginator;
    public function listForTenant(array $filters = []): LengthAwarePaginator;
    public function findForCustomer(int $id, User $user): Order;
    public function findForTenant(int $id): Order;
    public function placeOrder(User $user, array $data): Order;
    public function cancelOrder(int $id, User $user): Order;
    public function updateStatus(int $id, OrderStatus $status): Order;
}
