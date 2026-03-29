<?php

use App\Helpers\RouteHelper;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->group(function () {
        RouteHelper::includeRouteFiles(__DIR__ . '/Api/V1');

        Route::get('/ping', function () {
            return response()->json([
                'success' => true,
                'message' => 'API is running',
                'data' => ['version', '1.0.0']
            ]);
        });
    });
