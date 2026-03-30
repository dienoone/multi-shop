<?php

namespace App\Repositories;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Models\Tenant;
use Illuminate\Pagination\LengthAwarePaginator;

class TenantRepository implements TenantRepositoryInterface
{
    public function all(array $filters = []): LengthAwarePaginator
    {
        return Tenant::query()
            ->when(
                isset($filters['search']),
                fn($q) => $q->where(function ($q) use ($filters) {
                    $q->where('name', 'like', "%{$filters['search']}%")
                        ->orWhere('email', 'like', "%{$filters['search']}%")
                        ->orWhere('subdomain', 'like', "%{$filters['search']}%");
                })
            )
            ->when(
                isset($filters['plan']),
                fn($q) => $q->where('plan', $filters['plan'])
            )
            ->when(
                isset($filters['is_active']),
                fn($q) => $q->where('is_active', $filters['is_active'])
            )
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findById(int $id): ?Tenant
    {
        return Tenant::find($id);
    }

    public function findBySubdomain(string $subdomain): ?Tenant
    {
        return Tenant::where('subdomain', $subdomain)->first();
    }

    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        $tenant->update($data);
        return $tenant->fresh();
    }

    public function delete(Tenant $tenant): bool
    {
        return $tenant->delete();
    }
}
