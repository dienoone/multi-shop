<?php

use App\Http\Controllers\Api\V1\Store\CategoryController;
use Illuminate\Support\Facades\Route;

Route::get('categories',        [CategoryController::class, 'index']);
Route::get('categories/{id}',   [CategoryController::class, 'show']);

Route::middleware(['auth:sanctum', 'permission:products.create'])
    ->group(function () {
        Route::post('categories',           [CategoryController::class, 'store']);
        Route::put('categories/{id}',       [CategoryController::class, 'update']);
        Route::delete('categories/{id}',    [CategoryController::class, 'destroy']);
    });
