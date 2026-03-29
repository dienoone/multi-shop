<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->controller(AuthController::class)
    ->group(function () {

        // public routes
        Route::post('/register',                  'register');
        Route::post('/login',                     'login')->middleware('throttle:auth-sensitive');
        Route::get('/email/verify/{id}/{hash}',   'verifyEmail')->name('verification.verify');
        Route::post('/password/forgot',           'forgotPassword')->middleware('throttle:auth-sensitive');
        Route::post('/password/reset',            'resetPassword')->middleware('throttle:auth-sensitive');
        Route::post('/social/{provider}/token',   'socialToken');


        // Protected routes
        Route::middleware('auth:sanctum')
            ->group(function () {
                Route::post('/logout',          'logout');
                Route::get('/me',               'me');
                Route::post('/email/resend',    'resendVerification');

                Route::post('/password/change', 'changePassword');
            });
    });
