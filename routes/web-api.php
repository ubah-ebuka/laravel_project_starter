<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\UserController;

Route::group(['prefix' => 'v1'], function() {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function() {
        Route::post('register', [UserController::class, 'register'])
        ->name('register');

        Route::post('login', [UserController::class, 'login'])
            ->name('login');

        Route::middleware(['auth:sanctum', 'web-api', 'customer-auth'])->group(function() {
            Route::get('logout', [UserController::class, 'logout'])
            ->name('logout');

            Route::middleware(['email-verified', 'phone-verified'])->group(function() {
                Route::get('user', [UserController::class, 'user'])
                    ->name('user');
            });
        });
    });

    Route::group(['prefix' => 'password', 'as' => 'password.'], function() {
        Route::post('reset', [UserController::class, 'passwordReset'])
        ->name('reset');

        Route::post('verify', [UserController::class, 'passwordConfirmReset'])
        ->name('verify')->middleware('signed');
    });

    Route::middleware(['auth:sanctum', 'web-api', 'customer-auth'])->group(function() {
        Route::group(['prefix' => 'email', 'as' => 'email.'], function() {
            Route::get('send-verification', [UserController::class, 'sendEmailVerificationOtp'])
            ->name('sendVerification');
        
            Route::get('verify', [UserController::class, 'confirmEmailVerification'])
            ->name('verify')->middleware('signed');
        });
    
        Route::group(['prefix' => 'phone', 'as' => 'phone.'], function() {
            Route::get('send-verification', [UserController::class, 'sendPhoneNumberOtp'])
            ->name('sendVerification');
        
            Route::post('verify', [UserController::class, 'confirmPhoneVerification'])
            ->name('verify');
        });
    });

    Route::middleware(['auth:sanctum', 'web-api', 'customer-auth', 'email-verified', 'phone-verified'])->group(function() {
        // Protected routes can be added here
        Route::get('password/change', [UserController::class, 'passwordChange'])
                ->name('password.change');
    });
});