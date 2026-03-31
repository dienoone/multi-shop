<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function allForCustomer(User $user, array $filters = []): LengthAwarePaginator;
    public function allForTenant(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?Order;
    public function findByIdForCustomer(int $id, User $user): ?Order;
    public function create(array $data): Order;
    public function update(Order $order, array $data): Order;
}
