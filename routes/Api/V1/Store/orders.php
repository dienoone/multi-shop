<?php

use App\Http\Controllers\Api\V1\Store\AdminOrderController;
use App\Http\Controllers\Api\V1\Store\OrderController;
use Illuminate\Support\Facades\Route;

// customer routes
Route::middleware(['auth:sanctum', 'permission:orders.place'])
    ->group(function () {
        Route::get('orders',                [OrderController::class, 'index']);
        Route::post('orders',               [OrderController::class, 'store']);
        Route::get('orders/{id}',           [OrderController::class, 'show']);
        Route::post('orders/{id}/cancel',   [OrderController::class, 'cancel']);
    });

// store admin routes
Route::middleware(['auth:sanctum', 'permission:orders.view-all'])
    ->prefix('admin')
    ->group(function () {
        Route::get('orders',                        [AdminOrderController::class, 'index']);
        Route::get('orders/{id}',                   [AdminOrderController::class, 'show']);
        Route::patch('orders/{id}/status',          [AdminOrderController::class, 'updateStatus']);
    });
