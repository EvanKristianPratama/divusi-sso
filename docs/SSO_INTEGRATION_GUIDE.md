# Panduan Integrasi SSO Divusi

Dokumentasi lengkap untuk mengintegrasikan aplikasi/modul Anda dengan sistem Single Sign-On (SSO) Divusi.

---

## 📋 Daftar Isi

1. [Overview](#overview)
2. [Arsitektur SSO](#arsitektur-sso)
3. [Prasyarat](#prasyarat)
4. [Mendaftarkan Aplikasi Baru](#mendaftarkan-aplikasi-baru)
5. [Implementasi di Aplikasi Client](#implementasi-di-aplikasi-client)
6. [API Reference](#api-reference)
7. [Contoh Implementasi](#contoh-implementasi)
8. [Security Best Practices](#security-best-practices)
9. [Troubleshooting](#troubleshooting)

---

## Overview

SSO Divusi menggunakan **token-based authentication** untuk memungkinkan user login sekali dan mengakses semua modul tanpa perlu login ulang.

### Fitur Utama

- ✅ **Single Sign-On** - Login sekali, akses semua modul
- ✅ **Token-based** - Secure one-time token dengan expiry
- ✅ **Firebase Identity** - Autentikasi via Google, GitHub, Apple, Facebook
- ✅ **Role-based** - Kontrol akses berbasis role
- ✅ **Session Management** - Laravel mengelola session & role

### Komponen Sistem

| Komponen | Fungsi |
|----------|--------|
| **SSO Server** | Pusat autentikasi, generate token |
| **Firebase** | Identity Provider (Google, GitHub, dll) |
| **Client Apps** | Modul yang mengonsumsi SSO (COBIT, PMO, HR, Finance) |

---

## Arsitektur SSO

```
┌─────────────────────────────────────────────────────────────────────┐
│                          USER BROWSER                                │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────┐
│                         SSO SERVER (Divusi)                          │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐              │
│  │   Firebase  │    │   Laravel   │    │   Database  │              │
│  │   (Identity)│───▶│  (Session)  │───▶│   (Users)   │              │
│  └─────────────┘    └─────────────┘    └─────────────┘              │
└─────────────────────────────────────────────────────────────────────┘
                                    │
                    ┌───────────────┼───────────────┐
                    ▼               ▼               ▼
            ┌───────────┐   ┌───────────┐   ┌───────────┐
            │   COBIT   │   │    PMO    │   │    HR     │
            │   Module  │   │   Module  │   │   Module  │
            └───────────┘   └───────────┘   └───────────┘
```

### Flow Autentikasi

```
1. User login di SSO Server via Firebase (Google/GitHub/etc)
2. User klik modul tujuan (misal: COBIT)
3. SSO Server generate one-time token (64 karakter, expire 5 menit)
4. User di-redirect ke: {callback_url}?token={token}
5. Client App validate token ke SSO Server
6. SSO Server return user data jika token valid
7. Client App create local session
8. Token otomatis di-invalidate (one-time use)
```

---

## Prasyarat

Sebelum mengintegrasikan, pastikan:

- [ ] Aplikasi Anda sudah terdaftar di SSO Server
- [ ] Anda memiliki `callback_url` yang bisa diakses SSO Server
- [ ] Anda bisa melakukan HTTP request ke SSO Server
- [ ] SSL/HTTPS untuk production environment

---

## Mendaftarkan Aplikasi Baru

### Langkah 1: Edit Config di SSO Server

Buka file `config/sso.php` di SSO Server dan tambahkan aplikasi baru:

```php
<?php

return [
    'token_expiry_minutes' => (int) env('SSO_TOKEN_EXPIRY', 5),

    'apps' => [
        // Aplikasi existing...
        'cobit' => [
            'name' => 'COBIT Assessment',
            'description' => 'Sistem penilaian dan audit tata kelola IT',
            'callback_url' => env('SSO_COBIT_URL', 'http://cobit.divusi.local/sso/callback'),
            'enabled' => true,
        ],
        
        // 👇 TAMBAHKAN APLIKASI BARU DI SINI
        'inventory' => [
            'name' => 'Inventory Management',
            'description' => 'Sistem pengelolaan inventaris dan stok barang',
            'callback_url' => env('SSO_INVENTORY_URL', 'http://inventory.divusi.local/sso/callback'),
            'enabled' => true,
        ],
    ],
];
```

### Langkah 2: Tambahkan Environment Variable

Edit file `.env` di SSO Server:

```env
SSO_INVENTORY_URL=https://inventory.divusi.com/sso/callback
```

### Langkah 3: Clear Config Cache

```bash
php artisan config:clear
php artisan cache:clear
```

### Langkah 4: Verifikasi

Buka Dashboard SSO dan pastikan aplikasi baru muncul di daftar modul.

---

## Implementasi di Aplikasi Client

### Langkah 1: Buat Callback Route

Aplikasi Anda harus memiliki endpoint untuk menerima redirect dari SSO Server.

**Laravel:**
```php
// routes/web.php
Route::get('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
```

**Express.js:**
```javascript
// routes/sso.js
router.get('/sso/callback', ssoController.callback);
```

### Langkah 2: Buat Controller untuk Handle Callback

Logika utama:
1. Terima token dari query parameter
2. Validate token ke SSO Server
3. Create local session jika valid

Lihat [Contoh Implementasi](#contoh-implementasi) untuk kode lengkap.

### Langkah 3: Buat Middleware untuk Protected Routes

Pastikan routes yang memerlukan autentikasi dicek session-nya.

### Langkah 4: Logout Handler

Saat user logout dari aplikasi Anda, redirect ke SSO Server untuk logout global (opsional).

---

## API Reference

### Base URL

| Environment | URL |
|-------------|-----|
| Development | `http://localhost:8000` |
| Production | `https://sso.divusi.com` |

---

### 1. Validate Token

Endpoint untuk memvalidasi SSO token dan mendapatkan data user.

**Endpoint:**
```
POST /api/sso/validate
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
    "token": "a1b2c3d4e5f6g7h8i9j0..."  // 64 karakter
}
```

**Success Response (200):**
```json
{
    "status": "success",
    "user": {
        "id": 1,
        "firebase_uid": "abc123xyz",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "admin",
        "status": "active"
    }
}
```

**Error Response (401):**
```json
{
    "status": "error",
    "message": "Token tidak valid atau sudah expired"
}
```

**Catatan:**
- Token bersifat **one-time use** - setelah divalidasi, token akan di-invalidate
- Token memiliki masa berlaku **5 menit** (dapat dikonfigurasi)
- Token hanya valid untuk aplikasi yang sesuai

---

### 2. Generate Token (API)

Endpoint untuk generate token via API (memerlukan autentikasi).

**Endpoint:**
```
POST /api/sso/generate
```

**Headers:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {session_token}
```

**Request Body:**
```json
{
    "app": "cobit"
}
```

**Success Response (200):**
```json
{
    "status": "success",
    "token": "a1b2c3d4e5f6g7h8i9j0...",
    "redirect_url": "https://cobit.divusi.com/sso/callback?token=a1b2c3d4...",
    "expires_at": "2026-02-03T10:05:00+07:00"
}
```

**Error Response (404):**
```json
{
    "status": "error",
    "message": "Aplikasi tidak tersedia"
}
```

---

### 3. Redirect ke Aplikasi (Web)

Endpoint untuk redirect user ke aplikasi tujuan dengan token.

**Endpoint:**
```
GET /sso/redirect/{app}
```

**Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| `app` | string | Kode aplikasi (cobit, pmo, hr, finance) |

**Response:**
- Redirect 302 ke `{callback_url}?token={token}`

**Contoh:**
```
GET /sso/redirect/cobit
→ Redirect ke: https://cobit.divusi.com/sso/callback?token=abc123...
```

---

## Contoh Implementasi

### Laravel (PHP)

#### 1. Install HTTP Client

```bash
composer require guzzlehttp/guzzle
```

#### 2. Buat Config

```php
// config/sso.php
<?php

return [
    'server_url' => env('SSO_SERVER_URL', 'https://sso.divusi.com'),
    'app_key' => env('SSO_APP_KEY', 'cobit'),
];
```

#### 3. Buat Service

```php
// app/Services/SsoClientService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SsoClientService
{
    private string $serverUrl;

    public function __construct()
    {
        $this->serverUrl = config('sso.server_url');
    }

    /**
     * Validate SSO token dan return user data
     */
    public function validateToken(string $token): ?array
    {
        try {
            $response = Http::timeout(10)
                ->post("{$this->serverUrl}/api/sso/validate", [
                    'token' => $token,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    return $data['user'];
                }
            }

            Log::warning('SSO validation failed', [
                'token_prefix' => substr($token, 0, 8) . '...',
                'response' => $response->json(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('SSO validation error', [
                'message' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Get SSO login URL
     */
    public function getLoginUrl(): string
    {
        return $this->serverUrl . '/login';
    }

    /**
     * Get SSO logout URL
     */
    public function getLogoutUrl(): string
    {
        return $this->serverUrl . '/logout';
    }
}
```

#### 4. Buat Controller

```php
// app/Http/Controllers/SsoController.php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SsoClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SsoController extends Controller
{
    public function __construct(
        private SsoClientService $ssoService
    ) {}

    /**
     * Handle SSO callback dari server
     */
    public function callback(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('login')
                ->with('error', 'Token SSO tidak ditemukan');
        }

        // Validate token ke SSO Server
        $userData = $this->ssoService->validateToken($token);

        if (!$userData) {
            return redirect()->route('login')
                ->with('error', 'Token SSO tidak valid atau sudah expired');
        }

        // Find or create user di local database
        $user = User::updateOrCreate(
            ['email' => $userData['email']],
            [
                'name' => $userData['name'],
                'sso_id' => $userData['id'],
                'firebase_uid' => $userData['firebase_uid'],
                'role' => $userData['role'],
            ]
        );

        // Login user
        Auth::login($user, true);

        return redirect()->intended('/dashboard')
            ->with('success', 'Berhasil login via SSO');
    }

    /**
     * Redirect ke SSO login
     */
    public function login()
    {
        return redirect()->away($this->ssoService->getLoginUrl());
    }

    /**
     * Logout dan redirect ke SSO
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($this->ssoService->getLogoutUrl());
    }
}
```

#### 5. Buat Routes

```php
// routes/web.php
use App\Http\Controllers\SsoController;

// SSO Routes
Route::get('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');
Route::get('/sso/login', [SsoController::class, 'login'])->name('sso.login');
Route::post('/sso/logout', [SsoController::class, 'logout'])->name('sso.logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // ... routes lainnya
});
```

#### 6. Update User Model

```php
// app/Models/User.php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'sso_id',
        'firebase_uid',
        'role',
    ];

    protected $hidden = [
        'remember_token',
    ];
}
```

#### 7. Buat Migration

```bash
php artisan make:migration add_sso_fields_to_users_table
```

```php
// database/migrations/xxxx_add_sso_fields_to_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('sso_id')->nullable()->after('id');
            $table->string('firebase_uid')->nullable()->after('sso_id');
            $table->string('role')->default('user')->after('email');
            
            $table->index('sso_id');
            $table->index('firebase_uid');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_id', 'firebase_uid', 'role']);
        });
    }
};
```

#### 8. Environment Variables

```env
# .env
SSO_SERVER_URL=https://sso.divusi.com
SSO_APP_KEY=cobit
```

---

### Node.js (Express)

#### 1. Install Dependencies

```bash
npm install axios express-session
```

#### 2. Buat SSO Service

```javascript
// services/ssoService.js
const axios = require('axios');

const SSO_SERVER_URL = process.env.SSO_SERVER_URL || 'https://sso.divusi.com';

class SsoService {
    async validateToken(token) {
        try {
            const response = await axios.post(`${SSO_SERVER_URL}/api/sso/validate`, {
                token: token
            }, {
                timeout: 10000,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (response.data.status === 'success') {
                return response.data.user;
            }

            return null;
        } catch (error) {
            console.error('SSO validation error:', error.message);
            return null;
        }
    }

    getLoginUrl() {
        return `${SSO_SERVER_URL}/login`;
    }

    getLogoutUrl() {
        return `${SSO_SERVER_URL}/logout`;
    }
}

module.exports = new SsoService();
```

#### 3. Buat Controller

```javascript
// controllers/ssoController.js
const ssoService = require('../services/ssoService');
const User = require('../models/User');

exports.callback = async (req, res) => {
    const { token } = req.query;

    if (!token) {
        return res.redirect('/login?error=Token tidak ditemukan');
    }

    try {
        // Validate token ke SSO Server
        const userData = await ssoService.validateToken(token);

        if (!userData) {
            return res.redirect('/login?error=Token tidak valid');
        }

        // Find or create user
        let user = await User.findOne({ email: userData.email });
        
        if (!user) {
            user = await User.create({
                name: userData.name,
                email: userData.email,
                ssoId: userData.id,
                firebaseUid: userData.firebase_uid,
                role: userData.role
            });
        } else {
            user.name = userData.name;
            user.role = userData.role;
            await user.save();
        }

        // Set session
        req.session.userId = user._id;
        req.session.user = {
            id: user._id,
            name: user.name,
            email: user.email,
            role: user.role
        };

        res.redirect('/dashboard');
    } catch (error) {
        console.error('SSO callback error:', error);
        res.redirect('/login?error=Terjadi kesalahan');
    }
};

exports.login = (req, res) => {
    res.redirect(ssoService.getLoginUrl());
};

exports.logout = (req, res) => {
    req.session.destroy((err) => {
        res.redirect(ssoService.getLogoutUrl());
    });
};
```

#### 4. Buat Routes

```javascript
// routes/sso.js
const express = require('express');
const router = express.Router();
const ssoController = require('../controllers/ssoController');

router.get('/callback', ssoController.callback);
router.get('/login', ssoController.login);
router.get('/logout', ssoController.logout);

module.exports = router;
```

#### 5. Setup Express App

```javascript
// app.js
const express = require('express');
const session = require('express-session');
const ssoRoutes = require('./routes/sso');

const app = express();

// Session middleware
app.use(session({
    secret: process.env.SESSION_SECRET,
    resave: false,
    saveUninitialized: false,
    cookie: { 
        secure: process.env.NODE_ENV === 'production',
        maxAge: 24 * 60 * 60 * 1000 // 24 hours
    }
}));

// SSO Routes
app.use('/sso', ssoRoutes);

// Auth middleware
const requireAuth = (req, res, next) => {
    if (!req.session.userId) {
        return res.redirect('/sso/login');
    }
    next();
};

// Protected routes
app.get('/dashboard', requireAuth, (req, res) => {
    res.render('dashboard', { user: req.session.user });
});

app.listen(3000);
```

---

### Python (Flask)

```python
# sso_service.py
import requests
import os

SSO_SERVER_URL = os.getenv('SSO_SERVER_URL', 'https://sso.divusi.com')

def validate_token(token):
    try:
        response = requests.post(
            f'{SSO_SERVER_URL}/api/sso/validate',
            json={'token': token},
            timeout=10
        )
        
        if response.status_code == 200:
            data = response.json()
            if data.get('status') == 'success':
                return data.get('user')
        
        return None
    except Exception as e:
        print(f'SSO validation error: {e}')
        return None
```

```python
# app.py
from flask import Flask, redirect, request, session, url_for
from sso_service import validate_token

app = Flask(__name__)
app.secret_key = os.getenv('SECRET_KEY')

@app.route('/sso/callback')
def sso_callback():
    token = request.args.get('token')
    
    if not token:
        return redirect('/login?error=Token tidak ditemukan')
    
    user_data = validate_token(token)
    
    if not user_data:
        return redirect('/login?error=Token tidak valid')
    
    # Set session
    session['user'] = {
        'id': user_data['id'],
        'name': user_data['name'],
        'email': user_data['email'],
        'role': user_data['role']
    }
    
    return redirect('/dashboard')

@app.route('/dashboard')
def dashboard():
    if 'user' not in session:
        return redirect('/sso/login')
    
    return render_template('dashboard.html', user=session['user'])
```

---

## Security Best Practices

### 1. HTTPS Only

Selalu gunakan HTTPS di production untuk mencegah token interception.

```env
# .env
APP_ENV=production
SSO_SERVER_URL=https://sso.divusi.com
```

### 2. Token Validation

- ✅ Selalu validate token ke SSO Server
- ✅ Jangan simpan token di client-side storage
- ✅ Token bersifat one-time use

### 3. CSRF Protection

Pastikan callback endpoint terlindungi dari CSRF jika menggunakan POST.

### 4. Rate Limiting

Implementasikan rate limiting untuk mencegah brute force.

```php
// Laravel
Route::middleware('throttle:10,1')->group(function () {
    Route::get('/sso/callback', [SsoController::class, 'callback']);
});
```

### 5. Logging

Log semua aktivitas SSO untuk audit trail.

```php
Log::info('SSO login successful', [
    'user_id' => $user->id,
    'email' => $user->email,
    'ip' => request()->ip(),
]);
```

### 6. Session Regeneration

Regenerate session ID setelah login untuk mencegah session fixation.

```php
$request->session()->regenerate();
```

---

## Troubleshooting

### Token Tidak Valid

**Penyebab:**
- Token sudah expired (> 5 menit)
- Token sudah digunakan (one-time use)
- Token tidak untuk aplikasi ini

**Solusi:**
- Redirect user kembali ke SSO untuk login ulang
- Cek log di SSO Server

### Connection Timeout

**Penyebab:**
- SSO Server tidak bisa diakses
- Network issue

**Solusi:**
- Cek konektivitas ke SSO Server
- Implementasikan retry logic

```php
$response = Http::retry(3, 100)->timeout(10)
    ->post("{$this->serverUrl}/api/sso/validate", [
        'token' => $token,
    ]);
```

### CORS Error

**Penyebab:**
- API dipanggil dari browser (frontend)

**Solusi:**
- Selalu panggil API dari backend
- Token validation harus server-to-server

### User Tidak Ditemukan

**Penyebab:**
- User belum pernah login ke SSO

**Solusi:**
- Redirect ke SSO login page
- Buat user baru di local database saat callback

---

## FAQ

### Q: Apakah perlu sync database user?

**A:** Tidak perlu. Buat user di local database saat pertama kali callback berhasil. Update data user setiap callback untuk menjaga sinkronisasi.

### Q: Bagaimana handle logout global?

**A:** Saat user logout di salah satu modul, redirect ke SSO logout. SSO akan invalidate session dan redirect ke login page.

### Q: Apakah token bisa digunakan berkali-kali?

**A:** Tidak. Token bersifat one-time use dan otomatis di-invalidate setelah divalidasi.

### Q: Berapa lama token berlaku?

**A:** Default 5 menit. Dapat dikonfigurasi di `config/sso.php` dengan key `token_expiry_minutes`.

### Q: Bagaimana jika SSO Server down?

**A:** Implementasikan fallback atau tampilkan maintenance page. Session yang sudah ada di modul tetap valid hingga expired.

---

## Support

Jika mengalami masalah atau butuh bantuan:

- 📧 Email: developer@divusi.com
- 📚 Dokumentasi: https://docs.divusi.com/sso
- 💬 Slack: #sso-support

---

**Divusi SSO** - Single Sign-On for Enterprise
