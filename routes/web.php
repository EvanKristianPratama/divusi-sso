<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SsoController;
use App\Models\Module;
use App\Services\AuthService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Auth/Login', [
        'firebaseConfig' => [
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'storageBucket' => config('services.firebase.storage_bucket'),
            'messagingSenderId' => config('services.firebase.messaging_sender_id'),
            'appId' => config('services.firebase.app_id'),
        ],
    ]);
})->name('home');

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Auth/Login', [
        'firebaseConfig' => [
            'apiKey' => config('services.firebase.api_key'),
            'authDomain' => config('services.firebase.auth_domain'),
            'projectId' => config('services.firebase.project_id'),
            'storageBucket' => config('services.firebase.storage_bucket'),
            'messagingSenderId' => config('services.firebase.messaging_sender_id'),
            'appId' => config('services.firebase.app_id'),
        ],
    ]);
})->name('login');

// Firebase Auth Callback
Route::post('/auth/firebase/callback', function (AuthService $auth) {
    $idToken = request('id_token');
    $provider = request('provider', 'google');
    
    if (!$idToken) {
        return back()->withErrors(['error' => 'Token tidak valid']);
    }
    
    try {
        $result = $auth->loginWithFirebase($idToken, $provider);
        
        if ($result['success']) {
            // Redirect admin ke admin dashboard
            if ($result['is_admin'] ?? false) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('dashboard');
        }
        
        return redirect('/login')
            ->with('error', $result['message'])
            ->with('status', $result['status'] ?? 'error');
    } catch (\Exception $e) {
        return redirect('/login')->with('error', $e->getMessage());
    }
})->name('auth.firebase.callback');

// Auth Routes (User yang sudah approved)
Route::middleware('auth')->group(function () {
    // Dashboard - hanya tampilkan modul yang user punya akses
    Route::get('/dashboard', function () {
        $user = auth()->user();
        
        // Admin bisa akses semua modul
        if ($user->isAdmin()) {
            $modules = Module::active()->ordered()->get();
        } else {
            $modules = $user->modules()->where('is_active', true)->ordered()->get();
        }

        $apps = $modules->map(fn($m) => [
            'key' => $m->key,
            'name' => $m->name,
            'description' => $m->description,
            'url' => $m->url,
            'icon' => $m->icon,
            'color' => $m->color,
        ])->values()->all();

        return Inertia::render('User/Dashboard/Index', [
            'user' => [
                'id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'role' => $user->role,
                'is_admin' => $user->isAdmin(),
                'created_at' => $user->created_at,
            ],
            'apps' => $apps,
        ]);
    })->name('dashboard');

    // User Profile
    Route::get('/profile', function () {
        return Inertia::render('User/Profile/Index', [
            'user' => auth()->user()
        ]);
    })->name('profile');
    
    Route::post('/logout', function (AuthService $auth) {
        $auth->logout();
        return redirect('/login');
    })->name('logout');

    // Portal Redirect ke Modul
    Route::get('/app/{app}', [SsoController::class, 'redirect'])->name('app.redirect');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // User Management
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminController::class, 'userShow'])->name('users.show');
    Route::put('/users/{user}', [AdminController::class, 'userUpdate'])->name('users.update');
    Route::delete('/users/{user}', [AdminController::class, 'userDestroy'])->name('users.destroy');
    Route::post('/users/{user}/approve', [AdminController::class, 'userApprove'])->name('users.approve');
    Route::post('/users/{user}/reject', [AdminController::class, 'userReject'])->name('users.reject');
    Route::post('/users/{user}/modules', [AdminController::class, 'userAssignModules'])->name('users.modules');
    Route::post('/users/bulk-approve', [AdminController::class, 'userBulkApprove'])->name('users.bulk-approve');
    Route::post('/users/bulk-reject', [AdminController::class, 'userBulkReject'])->name('users.bulk-reject');
    Route::post('/users/pre-register', [AdminController::class, 'userPreRegister'])->name('users.pre-register');
    
    // Module Management
    Route::get('/modules', [AdminController::class, 'modules'])->name('modules');
    Route::post('/modules', [AdminController::class, 'moduleStore'])->name('modules.store'); // NEW
    Route::put('/modules/{module}', [AdminController::class, 'moduleUpdate'])->name('modules.update');
    Route::delete('/modules/{module}', [AdminController::class, 'moduleDestroy'])->name('modules.destroy'); // NEW
    Route::post('/modules/{module}/toggle', [AdminController::class, 'moduleToggle'])->name('modules.toggle');
    Route::post('/modules/sync', [AdminController::class, 'modulesSync'])->name('modules.sync');
});
