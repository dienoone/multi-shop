<?php

namespace App\Contracts\Repositories;

use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

interface TenantRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator;
    public function findById(int $id): ?Tenant;
    public function findBySubdomain(string $subdomain): ?Tenant;
    public function create(array $data): Tenant;
    public function update(Tenant $tenant, array $data): Tenant;
    public function delete(Tenant $tenant): bool;
}
