<?php

namespace App\Providers;

use App\Contracts\Repositories\AuthRepositoryInterface;
use App\Contracts\Services\AuthServiceInterface;
use App\Repositories\AuthRepository;
use App\Services\AuthService;
use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Services\TenantServiceInterface;
use App\Repositories\TenantRepository;
use App\Services\TenantService;
use Illuminate\Support\ServiceProvider;

class AppBindingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Auth
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AuthServiceInterface::class, AuthService::class);

        // Tenant
        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
        $this->app->bind(TenantServiceInterface::class, TenantService::class);
    }
}
