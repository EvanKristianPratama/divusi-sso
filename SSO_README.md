# Divusi SSO - Firebase + Laravel Architecture

## 🎯 Arsitektur

```
Firebase Auth (identity provider)
    ↓
Laravel Auth (session + role + access control)
    ↓
1 Platform → banyak modul (COBIT, PM, dll)
```

## 🔐 Auth Flow

```
1. Frontend: User login dengan email/password
   ↓
2. Firebase: Autentikasi user, return ID Token
   ↓
3. Frontend: Kirim ID Token ke Backend
   ↓
4. Backend: Verify token di /api/auth/login
   ↓
5. Backend: Find or create user di mst_users
   ↓
6. Backend: Check status & deleted_at
   ↓
7. Backend: Auth::login($user) → Session aktif
   ↓
8. Frontend: User logged in, akses ke modul
```

## 📁 Struktur Project

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Auth/
│   │       └── AuthFirebaseController.php      # Login/Register/Logout logic
│   └── Requests/
│       └── FirebaseLoginRequest.php             # Validasi Firebase token
├── Models/
│   └── User.php                                 # User model (mst_users)
├── Services/
│   └── Firebase/
│       └── FirebaseService.php                  # Firebase token verification
├── Exceptions/
│   └── FirebaseTokenException.php               # Custom exception
└── Providers/
    └── AppServiceProvider.php                   # Firebase service provider

config/
└── firebase.php                                 # Firebase configuration

database/
├── migrations/
│   └── 0001_01_01_000000_create_users_table.php # mst_users table
└── factories/
    └── UserFactory.php                          # User factory

resources/
└── js/
    └── firebase.js                              # Firebase client SDK

routes/
└── api.php                                      # API routes
```

## 🚀 Setup

### 1. Install Dependencies

```bash
# Backend
composer require kreait/firebase-php

# Frontend
npm install firebase
```

### 2. Firebase Configuration

**Download service account JSON dari Firebase Console:**
- Go to: Firebase Console → Project Settings → Service Accounts
- Click "Generate New Private Key"
- Save ke: `storage/app/firebase/service-account.json`

**Update .env:**
```env
FIREBASE_API_KEY=AIzaSyAwAYRGXRzcOdQT1LRjhMBmIBCWupEE81s
FIREBASE_AUTH_DOMAIN=divusisso.firebaseapp.com
FIREBASE_PROJECT_ID=divusisso
FIREBASE_STORAGE_BUCKET=divusisso.firebasestorage.app
FIREBASE_MESSAGING_SENDER_ID=290764710306
FIREBASE_APP_ID=1:290764710306:web:dc2ca8f64557a3181552fb
FIREBASE_CREDENTIALS=/path/to/service-account.json

# Frontend
VITE_FIREBASE_API_KEY=AIzaSyAwAYRGXRzcOdQT1LRjhMBmIBCWupEE81s
VITE_FIREBASE_AUTH_DOMAIN=divusisso.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=divusisso
VITE_FIREBASE_STORAGE_BUCKET=divusisso.firebasestorage.app
VITE_FIREBASE_MESSAGING_SENDER_ID=290764710306
VITE_FIREBASE_APP_ID=1:290764710306:web:dc2ca8f64557a3181552fb
```

### 3. Database Migration

```bash
php artisan migrate
```

## 📚 API Endpoints

### Public Endpoints

**POST /api/auth/register**
```json
{
  "id_token": "firebase_id_token_here"
}
```

Response (201):
```json
{
  "message": "Registrasi berhasil",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "role": "user",
    "status": "active"
  },
  "status": "success"
}
```

**POST /api/auth/login**
```json
{
  "id_token": "firebase_id_token_here"
}
```

Response (200):
```json
{
  "message": "Login berhasil",
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "role": "user",
    "status": "active"
  },
  "status": "success"
}
```

### Protected Endpoints

**POST /api/auth/logout** (requires auth)
```
Authorization: Bearer {token}
```

Response (200):
```json
{
  "message": "Logout berhasil",
  "status": "success"
}
```

**GET /api/auth/me** (requires auth)
```
Authorization: Bearer {token}
```

Response (200):
```json
{
  "user": {
    "id": 1,
    "name": "User Name",
    "email": "user@example.com",
    "role": "user",
    "status": "active"
  },
  "status": "success"
}
```

## 🧠 Key Principles

### ❌ TIDAK BOLEH
- ❌ Pakai `Auth::attempt()` dengan password lokal
- ❌ Simpan password di database
- ❌ Bikin login kedua (non-Firebase)
- ❌ Bikin user table lain

### ✅ HARUS
- ✅ Firebase token verification sebelum login
- ✅ Check `status` dan `deleted_at` sebelum login
- ✅ Semua user dari `mst_users`
- ✅ Semua modul relasi ke `mst_users.id`

## 📖 Frontend Integration

```javascript
import { firebaseLogin, firebaseLogout, firebaseRegister } from './firebase.js';

// Register
await firebaseRegister('user@example.com', 'password');

// Login
await firebaseLogin('user@example.com', 'password');

// Get current user
const user = auth.currentUser;

// Logout
await firebaseLogout();
```

## 🔍 Testing

```bash
# Run migrations
php artisan migrate

# Run factory
php artisan tinker
>>> User::factory()->create();

# Test endpoint
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"id_token":"your_firebase_token"}'
```

## 🎯 Next Steps

1. ✅ Database setup
2. ✅ Auth backend bridge
3. ⏳ Frontend Firebase login UI
4. ⏳ Modul COBIT
5. ⏳ Modul PM

---

**Ingat:** Laravel tidak mengotentikasi user, Laravel menerima user yang sudah terotentikasi.
