<?php

use App\Http\Controllers\Api\V1\Admin\TenantController;
use Illuminate\Support\Facades\Route;

Route::apiResource('tenants', TenantController::class);
