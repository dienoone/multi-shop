<?php

namespace App\Services;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Services\TenantServiceInterface;
use App\Models\Tenant;
use App\Models\User;
use App\Enums\RoleType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TenantService implements TenantServiceInterface
{
    public function __construct(
        protected TenantRepositoryInterface $tenantRepository,
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->tenantRepository->all($filters);
    }

    public function findById(int $id): Tenant
    {
        $tenant = $this->tenantRepository->findById($id);

        throw_if(
            !$tenant,
            ModelNotFoundException::class,
            "Tenant not found."
        );

        return $tenant;
    }

    public function create(array $data): Tenant
    {
        // Ensure subdomain is unique
        throw_if(
            $this->tenantRepository->findBySubdomain($data['subdomain']),
            ValidationException::withMessages([
                'subdomain' => 'This subdomain is already taken.',
            ])
        );

        return DB::transaction(function () use ($data) {
            $tenant = $this->tenantRepository->create([
                'name'      => $data['name'],
                'subdomain' => $data['subdomain'],
                'email'     => $data['email'],
                'plan'      => $data['plan'] ?? 'free',
                'is_active' => $data['is_active'] ?? true,
                'settings'  => $data['settings'] ?? null,
            ]);

            // Create the store owner account
            $owner = User::create([
                'tenant_id'         => $tenant->id,
                'name'              => $data['owner_name'],
                'email'             => $data['owner_email'],
                'password'          => Hash::make($data['owner_password']),
                'email_verified_at' => now(), // auto-verify store owners
            ]);

            $owner->assignRole(RoleType::SuperAdmin->value);

            return $tenant;
        });
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        // If subdomain is being changed, ensure it's not taken by another tenant
        if (isset($data['subdomain']) && $data['subdomain'] !== $tenant->subdomain) {
            throw_if(
                $this->tenantRepository->findBySubdomain($data['subdomain']),
                ValidationException::withMessages([
                    'subdomain' => 'This subdomain is already taken.',
                ])
            );
        }

        return $this->tenantRepository->update($tenant, $data);
    }

    public function delete(Tenant $tenant): void
    {
        $this->tenantRepository->delete($tenant);
    }
}
