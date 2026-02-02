<?php

use App\Http\Controllers\SsoController;
use App\Services\AuthService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::view('/', 'auth.login')->name('home');
    Route::view('/login', 'auth.login')->name('login');
});

// Auth Routes
Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    
    Route::post('/logout', function (AuthService $auth) {
        $auth->logout();
        return redirect('/login');
    })->name('logout');

    // SSO Redirect
    Route::get('/sso/redirect/{app}', [SsoController::class, 'redirect'])
        ->name('sso.redirect');
});
