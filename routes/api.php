<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Prefix: /api
*/

// Auth Routes (pakai web middleware untuk session)
Route::middleware('web')->prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

// SSO Routes
Route::prefix('sso')->group(function () {
    // Public: untuk apps lain validate token
    Route::post('/validate', [SsoController::class, 'validate']);
    
    // Protected: generate token (butuh auth)
    Route::middleware(['web', 'auth'])->group(function () {
        Route::post('/generate', [SsoController::class, 'generate']);
    });
});
