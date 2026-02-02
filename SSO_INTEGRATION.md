# Integrasi SSO Divusi ke Apps Lain

## Arsitektur

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   SSO Server    │     │   App COBIT     │     │    App PMO      │
│  (divusi.com)   │     │ (cobit.divusi)  │     │  (pmo.divusi)   │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ - Firebase Auth │     │ - Validate SSO  │     │ - Validate SSO  │
│ - User Database │     │ - Local Session │     │ - Local Session │
│ - Generate Token│     │ - COBIT Logic   │     │ - PMO Logic     │
└────────┬────────┘     └────────┬────────┘     └────────┬────────┘
         │                       │                       │
         └───────────────────────┴───────────────────────┘
                         API Communication
```

## Flow Integrasi

### 1. User Login via SSO
```
User → SSO Login → Firebase Auth → SSO Server → Dashboard
```

### 2. User Akses App Lain (COBIT)
```
User klik COBIT → SSO generate token → Redirect ke COBIT dengan token
→ COBIT validate token ke SSO → COBIT create local session
```

---

## Cara Integrasi di App Lain (COBIT/PMO/dll)

### Step 1: Buat Route Callback

```php
// routes/web.php di App COBIT
Route::get('/sso/callback', [SsoCallbackController::class, 'handle']);
```

### Step 2: Buat Controller untuk Handle SSO

```php
<?php
// app/Http/Controllers/SsoCallbackController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class SsoCallbackController extends Controller
{
    public function handle(Request $request)
    {
        $token = $request->query('token');
        
        if (!$token) {
            return redirect('/login')->with('error', 'Token tidak ditemukan');
        }

        // Validate token ke SSO Server
        $response = Http::post(config('sso.server_url') . '/api/sso/validate', [
            'token' => $token,
        ]);

        if (!$response->successful()) {
            return redirect('/login')->with('error', 'Token tidak valid');
        }

        $ssoUser = $response->json()['user'];

        // Find or create local user
        $user = User::updateOrCreate(
            ['sso_user_id' => $ssoUser['id']],
            [
                'name' => $ssoUser['name'],
                'email' => $ssoUser['email'],
                'role' => $ssoUser['role'],
            ]
        );

        // Login local
        Auth::login($user);

        return redirect('/dashboard');
    }
}
```

### Step 3: Config SSO Server URL

```php
// config/sso.php di App COBIT
return [
    'server_url' => env('SSO_SERVER_URL', 'http://localhost:8000'),
];
```

```env
# .env di App COBIT
SSO_SERVER_URL=http://localhost:8000
```

### Step 4: Middleware untuk Check SSO Session

```php
<?php
// app/Http/Middleware/SsoAuthenticate.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SsoAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            // Redirect ke SSO dengan callback URL
            $callbackUrl = urlencode(url('/sso/callback'));
            $ssoUrl = config('sso.server_url') . '/login?redirect=' . $callbackUrl;
            
            return redirect($ssoUrl);
        }

        return $next($request);
    }
}
```

---

## API Endpoints SSO Server

### 1. Validate Token
```
POST /api/sso/validate
Content-Type: application/json

{
    "token": "abc123..."
}

Response:
{
    "status": "success",
    "user": {
        "id": 1,
        "firebase_uid": "xxx",
        "name": "John Doe",
        "email": "john@example.com",
        "role": "admin"
    }
}
```

### 2. Generate Token (untuk redirect)
```
POST /api/sso/generate-token
Content-Type: application/json
Cookie: laravel_session=xxx

{
    "app": "cobit",
    "callback_url": "http://cobit.localhost/sso/callback"
}

Response:
{
    "status": "success",
    "token": "abc123...",
    "redirect_url": "http://cobit.localhost/sso/callback?sso_token=abc123...",
    "expires_in": 300
}
```

---

## Dashboard dengan Link ke Apps

Update dashboard.blade.php untuk redirect ke apps:

```html
<!-- Module card dengan SSO redirect -->
<a href="/sso/redirect/cobit" class="module-card">
    <div class="module-icon cobit">📊</div>
    <div class="module-name">COBIT</div>
</a>
```

---

## Konfigurasi .env SSO Server

```env
# URL Apps untuk redirect
APP_COBIT_URL=http://cobit.localhost:8001
APP_PMO_URL=http://pmo.localhost:8002
APP_HR_URL=http://hr.localhost:8003
APP_FINANCE_URL=http://finance.localhost:8004
APP_INVENTORY_URL=http://inventory.localhost:8005
APP_REPORT_URL=http://report.localhost:8006
```

---

## Security Notes

1. **Token sekali pakai** - Token expired setelah 5 menit atau setelah divalidasi
2. **HTTPS wajib** di production
3. **Whitelist callback URLs** - Hanya apps yang terdaftar bisa generate token
4. **Rate limiting** - Batasi request validate token

---

## Quick Test

```bash
# 1. Login di SSO
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"id_token": "firebase_token_here"}'

# 2. Generate SSO token
curl -X POST http://localhost:8000/api/sso/generate-token \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=xxx" \
  -d '{"app": "cobit", "callback_url": "http://cobit.localhost/sso/callback"}'

# 3. Validate token di app lain
curl -X POST http://localhost:8000/api/sso/validate \
  -H "Content-Type: application/json" \
  -d '{"token": "generated_token_here"}'
```
