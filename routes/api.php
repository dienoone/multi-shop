<?php

use App\Helpers\RouteHelper;
use App\Http\Controllers\Api\V1\Webhook\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/webhook/stripe', [StripeWebhookController::class, 'handle'])
    ->name('webhook.stripe');

Route::prefix('v1')
    ->group(function () {

        RouteHelper::includeRouteFiles(__DIR__ . '/Api/V1/Auth');

        // admin
        Route::prefix('admin')
            ->middleware(['auth:sanctum', 'role:super_admin'])
            ->group(function () {
                RouteHelper::includeRouteFiles(__DIR__ . '/Api/V1/Admin');
            });

        // tenant-scoped routes
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
