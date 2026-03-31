<?php

use App\Http\Controllers\Api\V1\Store\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('products',          [ProductController::class, 'index']);
Route::get('products/{id}',     [ProductController::class, 'show']);

Route::middleware(['auth:sanctum', 'permission:products.create'])
    ->group(function () {
        Route::post('products',             [ProductController::class, 'store']);
        Route::put('products/{id}',         [ProductController::class, 'update']);
        Route::delete('products/{id}',      [ProductController::class, 'destroy']);
    });
