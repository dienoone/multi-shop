<?php

use App\Http\Controllers\Api\V1\Store\CouponController;
use Illuminate\Support\Facades\Route;

// admin — manage coupons
Route::middleware(['auth:sanctum', 'permission:coupons.manage'])
    ->prefix('admin')
    ->group(function () {
        Route::apiResource('coupons', CouponController::class);
    });

// customer — validate a coupon before checkout
Route::middleware(['auth:sanctum', 'permission:coupons.apply'])
    ->group(function () {
        Route::post('coupons/apply', [CouponController::class, 'apply']);
    });
