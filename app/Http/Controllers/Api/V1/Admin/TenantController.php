<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Contracts\Services\TenantServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(
        protected TenantServiceInterface $tenantService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenants = $this->tenantService->list($request->only([
            'search',
            'plan',
            'is_active',
            'per_page'
        ]));

        return $this->paginated(TenantResource::collection($tenants));
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $tenant = $this->tenantService->create($request->validated());

        return $this->created(
            new TenantResource($tenant),
            'Tenant created successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);

        return $this->success(new TenantResource($tenant));
    }

    public function update(UpdateTenantRequest $request, int $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);
        $tenant = $this->tenantService->update($tenant, $request->validated());

        return $this->success(
            new TenantResource($tenant),
            'Tenant updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $tenant = $this->tenantService->findById($id);
        $this->tenantService->delete($tenant);

        return $this->noContent('Tenant deleted successfully.');
    }
}
