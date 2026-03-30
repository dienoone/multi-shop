<?php

namespace App\Contracts\Services;

use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

interface TenantServiceInterface
{
    public function list(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): Tenant;
    public function create(array $data): Tenant;
    public function update(Tenant $tenant, array $data): Tenant;
    public function delete(Tenant $tenant): void;
}
