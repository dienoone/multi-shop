<?php

use App\Helpers\RouteHelper;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function () {

        RouteHelper::includeRouteFiles(__DIR__ . '/Api/V1/Auth');

        // Super admin — no tenant context needed
        Route::prefix('admin')
            ->middleware(['auth:sanctum', 'role:super_admin'])
            ->group(function () {
                RouteHelper::includeRouteFiles(__DIR__ . '/Api/V1/Admin');
            });

        // All tenant-scoped routes
        Route::middleware(['tenant', 'tenant.active'])
            ->group(function () {
                RouteHelper::includeRouteFiles(__DIR__ . '/Api/V1/Store');
            });

        Route::get('/ping', fn() => response()->json([
            'success' => true,
            'message' => 'API is running',
            'data'    => ['version' => '1.0.0'],
        ]));
    });
