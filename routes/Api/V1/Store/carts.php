<?php

use App\Http\Controllers\Api\V1\Store\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'permission:cart.manage'])
    ->group(function () {
        Route::get('cart',                          [CartController::class, 'show']);
        Route::post('cart/items',                   [CartController::class, 'addItem']);
        Route::put('cart/items/{cartItem}',         [CartController::class, 'updateItem']);
        Route::delete('cart/items/{cartItem}',      [CartController::class, 'removeItem']);
        Route::delete('cart',                       [CartController::class, 'clear']);
    });
