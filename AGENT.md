# 🤖 AGENT GUIDELINES - Divusi SSO

> **BACA INI SEBELUM MELAKUKAN PERUBAHAN APAPUN!**
> File ini adalah panduan wajib untuk AI Agent yang bekerja di project ini.

---

## 📌 VISI PRODUK

### Apa itu Divusi SSO?
**Single Sign-On Platform** untuk ekosistem Divusi dengan prinsip:
- **Firebase = Identity Provider** (Google OAuth only, NO password)
- **Laravel = Session, Role, Access Control**
- **1 Platform, Banyak Modul** (COBIT, PMO, HR, Finance, dll)

### Tujuan Utama
1. User login SEKALI di SSO, bisa akses semua modul tanpa login ulang
2. Centralized user management (role, status, access)
3. Secure token-based cross-app authentication
4. Zero password storage (passwordless via Firebase)

### Target User
- Internal Divusi employees
- Multi-department access (IT, HR, Finance, Management)

---

## 🚫 DO NOT (LARANGAN KERAS)

### 1. JANGAN Buat Fitur Password
```php
// ❌ DILARANG
$user->password = Hash::make($password);
Auth::attempt(['email' => $email, 'password' => $password]);

// ✅ BENAR - Hanya Firebase
$this->authService->loginWithFirebase($idToken);
```

### 2. JANGAN Hardcode Credentials
```php
// ❌ DILARANG
$apiKey = 'AIzaSyB1234567890';
$dbPassword = 'secret123';

// ✅ BENAR - Gunakan .env
$apiKey = config('firebase.api_key');
$dbPassword = env('DB_PASSWORD');
```

### 3. JANGAN Skip Testing
```bash
# ❌ DILARANG - Commit tanpa test
git commit -m "add feature"

# ✅ BENAR - Test dulu
php artisan test
git commit -m "feat: add feature with tests"
```

### 4. JANGAN Ubah Naming Convention
```php
// ❌ DILARANG
$table = 'users';        // tanpa prefix
$table = 'tbl_users';    // prefix salah

// ✅ BENAR
$table = 'mst_users';    // master data
$table = 'trs_sso_tokens'; // transaction data
```

### 5. JANGAN Taruh Business Logic di Controller
```php
// ❌ DILARANG - Fat Controller
class UserController {
    public function store(Request $request) {
        $user = User::create([...]);
        Mail::send(...);
        Log::info(...);
        // 100 lines of code...
    }
}

// ✅ BENAR - Thin Controller, Logic di Service
class UserController {
    public function store(Request $request) {
        return $this->userService->create($request->validated());
    }
}
```

### 6. JANGAN Expose Sensitive Data
```php
// ❌ DILARANG
return response()->json($user); // expose semua field

// ✅ BENAR
return response()->json($user->only(['id', 'name', 'email', 'role']));
```

### 7. JANGAN Buat Migration yang Destructive tanpa Backup Plan
```php
// ❌ DILARANG di production
Schema::dropIfExists('mst_users');

// ✅ BENAR - Soft changes, reversible
Schema::table('mst_users', function ($table) {
    $table->string('new_column')->nullable();
});
```

---

## ✅ DO (WAJIB DILAKUKAN)

### 1. SELALU Ikuti Struktur Folder
```
app/
├── Models/          # Eloquent models only
├── Services/        # Business logic
├── Http/
│   ├── Controllers/ # Thin controllers
│   ├── Requests/    # Form validation
│   └── Middleware/  # Request filtering
├── Exceptions/      # Custom exceptions
└── Providers/       # Service providers

database/
├── migrations/      # Schema changes
├── seeders/         # Test/demo data
└── factories/       # Model factories

tests/
├── Unit/            # Unit tests
└── Feature/         # Integration tests
```

### 2. SELALU Buat Test untuk Setiap Perubahan

```php
// Setelah buat/edit Service
// tests/Unit/Services/AuthServiceTest.php
class AuthServiceTest extends TestCase
{
    public function test_login_with_valid_token(): void
    {
        // Arrange
        // Act
        // Assert
    }
}

// Setelah buat/edit Controller
// tests/Feature/Auth/LoginTest.php
class LoginTest extends TestCase
{
    public function test_login_endpoint_returns_user(): void
    {
        // Test HTTP request/response
    }
}
```

### 3. SELALU Gunakan Type Hints
```php
// ✅ BENAR
public function generateToken(User $user, string $app): ?SsoToken
{
    // ...
}

// ❌ SALAH
public function generateToken($user, $app)
{
    // ...
}
```

### 4. SELALU Validasi Input
```php
// ✅ BENAR - Pakai Form Request
class FirebaseLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id_token' => 'required|string|min:100',
        ];
    }
}

// Atau inline untuk simple cases
$request->validate(['app' => 'required|string|max:50']);
```

### 5. SELALU Handle Errors dengan Proper Response
```php
// ✅ BENAR
try {
    $result = $this->service->process();
    return response()->json(['data' => $result, 'status' => 'success']);
} catch (CustomException $e) {
    return response()->json(['message' => $e->getMessage(), 'status' => 'error'], 400);
} catch (\Exception $e) {
    Log::error('Unexpected error', ['error' => $e->getMessage()]);
    return response()->json(['message' => 'Terjadi kesalahan', 'status' => 'error'], 500);
}
```

### 6. SELALU Gunakan Dependency Injection
```php
// ✅ BENAR
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}
}

// ❌ SALAH
class AuthController extends Controller
{
    public function login()
    {
        $service = new AuthService(); // manual instantiation
    }
}
```

### 7. SELALU Clear Cache Setelah Config/Route Change
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

---

## 📋 NAMING CONVENTIONS

### Database Tables
| Prefix | Deskripsi | Contoh |
|--------|-----------|--------|
| `mst_` | Master data (static) | `mst_users`, `mst_roles`, `mst_apps` |
| `trs_` | Transaction data | `trs_sso_tokens`, `trs_audit_logs` |
| `ref_` | Reference/lookup | `ref_status`, `ref_countries` |
| `map_` | Mapping/pivot | `map_user_roles`, `map_app_permissions` |

### Model Names
```php
// Singular, PascalCase
User, SsoToken, AuditLog, AppPermission
```

### Service Names
```php
// {Domain}Service
AuthService, SsoService, UserService, NotificationService
```

### Controller Names
```php
// {Resource}Controller
AuthController, SsoController, UserController, DashboardController
```

### Method Names
```php
// Verbs: get, find, create, update, delete, validate, generate, format
public function generateToken(): SsoToken
public function validateToken(string $token): ?array
public function formatUserData(User $user): array
```

---

## 🧪 TESTING STANDARDS

### Minimum Test Coverage
- **Services**: 80% coverage
- **Controllers**: Feature tests for all endpoints
- **Models**: Test scopes, relationships, accessors

### Test Structure
```php
class SsoServiceTest extends TestCase
{
    use RefreshDatabase;

    private SsoService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SsoService::class);
        $this->user = User::factory()->create();
    }

    // Naming: test_{method}_{scenario}_{expected_result}
    public function test_generateToken_with_valid_app_returns_token(): void
    {
        // Arrange
        $app = 'cobit';

        // Act
        $token = $this->service->generateToken($this->user, $app);

        // Assert
        $this->assertNotNull($token);
        $this->assertEquals($app, $token->app);
        $this->assertDatabaseHas('trs_sso_tokens', ['user_id' => $this->user->id]);
    }

    public function test_generateToken_with_disabled_app_returns_null(): void
    {
        $token = $this->service->generateToken($this->user, 'disabled_app');
        $this->assertNull($token);
    }

    public function test_validateToken_with_expired_token_returns_null(): void
    {
        $token = SsoToken::factory()->expired()->create();
        $result = $this->service->validateToken($token->token);
        $this->assertNull($result);
    }
}
```

### Run Tests Before Commit
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=SsoServiceTest

# Run with coverage
php artisan test --coverage
```

---

## 🔐 SECURITY CHECKLIST

### Sebelum Deploy
- [ ] Tidak ada credentials di code
- [ ] `APP_DEBUG=false` di production
- [ ] `APP_ENV=production`
- [ ] HTTPS enabled
- [ ] CORS configured properly
- [ ] Rate limiting enabled
- [ ] Input validation di semua endpoint
- [ ] SQL injection prevention (use Eloquent/Query Builder)
- [ ] XSS prevention (escape output)
- [ ] CSRF protection enabled

### Firebase Security
- [ ] Service account JSON TIDAK di repository
- [ ] Authorized domains configured di Firebase Console
- [ ] Only Google provider enabled (no email/password)

---

## 📝 COMMIT CONVENTIONS

### Format
```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types
| Type | Deskripsi |
|------|-----------|
| `feat` | Fitur baru |
| `fix` | Bug fix |
| `refactor` | Code refactoring |
| `test` | Tambah/update tests |
| `docs` | Documentation |
| `style` | Formatting (no logic change) |
| `chore` | Maintenance tasks |

### Examples
```bash
feat(sso): add token expiry validation

- Add expires_at check in validateToken
- Return null if token expired
- Add unit test for expired token scenario

Closes #123
```

```bash
fix(auth): handle inactive user login attempt

- Check user.status before Auth::login
- Return 403 with proper message
- Add feature test
```

---

## 🔄 WORKFLOW CHECKLIST

### Sebelum Mulai Coding
- [ ] Baca AGENT.md ini
- [ ] Pahami existing code structure
- [ ] Identifikasi files yang perlu diubah
- [ ] Plan test cases

### Selama Coding
- [ ] Ikuti naming conventions
- [ ] Gunakan type hints
- [ ] Handle errors properly
- [ ] Tidak hardcode values

### Setelah Coding
- [ ] Buat/update tests
- [ ] Run `php artisan test`
- [ ] Clear caches jika perlu
- [ ] Run linter/formatter
- [ ] Review perubahan

### Sebelum Commit
- [ ] All tests pass
- [ ] No sensitive data exposed
- [ ] Commit message sesuai convention
- [ ] Update documentation jika perlu

---

## 🆘 TROUBLESHOOTING COMMON ISSUES

### Firebase Token Verification Failed
```php
// Check:
// 1. Service account JSON valid
// 2. Project ID match
// 3. Token not expired (5 min default)
```

### Session Not Persisting
```php
// Check:
// 1. SESSION_DRIVER=database
// 2. sessions table exists
// 3. API routes use 'web' middleware
```

### SSO Token Invalid
```php
// Check:
// 1. Token length = 64 chars
// 2. Token not used (used_at = null)
// 3. Token not expired (expires_at > now)
```

### Route Not Found
```bash
# Clear route cache
php artisan route:clear
php artisan route:list
```

---

## 📚 REFERENCE FILES

### Core Files
- `app/Services/AuthService.php` - Authentication logic
- `app/Services/SsoService.php` - SSO token logic
- `app/Services/Firebase/FirebaseService.php` - Firebase verification
- `config/sso.php` - SSO apps configuration

### Configuration
- `.env.example` - Environment template
- `config/firebase.php` - Firebase settings
- `config/auth.php` - Laravel auth config

### Documentation
- `SSO_INTEGRATION.md` - Integration guide for other apps
- `DEPLOYMENT_CHECKLIST.md` - Production deployment checklist

---

## ⚠️ FINAL REMINDER

> **SETIAP PERUBAHAN KODE HARUS:**
> 1. Ada test-nya (Unit atau Feature)
> 2. Lulus `php artisan test`
> 3. Tidak melanggar aturan di atas
> 4. Mengikuti naming convention
> 5. Di-commit dengan message yang proper

**Jika ragu, TANYA dulu sebelum implementasi!**
