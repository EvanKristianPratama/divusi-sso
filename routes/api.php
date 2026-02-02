<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthFirebaseController;
use App\Http\Controllers\Auth\SsoController;

// Auth routes - pakai web middleware untuk share session
Route::middleware(['web'])->group(function () {
    Route::post('/auth/register', [AuthFirebaseController::class, 'register']);
    Route::post('/auth/login', [AuthFirebaseController::class, 'login']);
    Route::post('/auth/logout', [AuthFirebaseController::class, 'logout']);
    Route::get('/auth/me', [AuthFirebaseController::class, 'me']);
    
    // SSO untuk integrasi ke apps lain
    Route::post('/sso/generate-token', [SsoController::class, 'generateToken']);
});

// SSO validation - stateless untuk apps eksternal
Route::post('/sso/validate', [SsoController::class, 'validateToken']);
