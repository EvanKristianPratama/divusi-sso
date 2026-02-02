<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SsoController;

// Login page
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Dashboard (protected)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// SSO redirect ke apps lain
Route::get('/sso/redirect/{app}', [SsoController::class, 'redirectToApp'])
    ->middleware('auth')
    ->name('sso.redirect');

// Welcome page
Route::get('/', function () {
    return view('welcome');
});
