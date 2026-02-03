# 🎨 AGENT FRONTEND GUIDE - Divusi SSO

> Dokumentasi khusus untuk AI Agent agar tidak keluar konteks saat bekerja dengan frontend.

---

## 📦 Tech Stack Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| Vue 3 | ^3.x | UI Framework (Composition API + `<script setup>`) |
| Inertia.js | ^2.x | SPA tanpa API - bridge Laravel ↔ Vue |
| Vite | ^7.x | Build tool & dev server |
| Tailwind CSS | ^4.x | Utility-first CSS |
| Firebase Auth | ^11.x | Client-side authentication |
| Headless UI | ^1.x | Unstyled accessible components |
| Heroicons | ^2.x | SVG icons from Tailwind team |

---

## 📁 Struktur Folder Frontend

```
resources/
├── js/
│   ├── app.js                    # Entry point - createInertiaApp
│   ├── bootstrap.js              # Axios setup
│   ├── Layouts/
│   │   ├── GuestLayout.vue       # Layout untuk halaman publik (TIDAK DIPAKAI)
│   │   └── AuthenticatedLayout.vue # Layout dengan navbar (LIGHT THEME)
│   └── Pages/
│       ├── Auth/
│       │   └── Login.vue         # Halaman login dengan social auth
│       ├── Admin/
│       │   └── Dashboard.vue     # Admin Dashboard (DARK MODE READY)
│       └── Dashboard.vue         # User Dashboard (LIGHT THEME)
├── css/
│   └── app.css                   # Tailwind imports
└── views/
    └── app.blade.php             # ROOT TEMPLATE INERTIA (JANGAN HAPUS!)
```

---

## 🎨 Design System

### Color Palette (Light Theme - Modern)
- **Primary**: Emerald/Teal gradient (`from-emerald-500 to-teal-600`)
- **Background**: Gray-50 (`bg-gray-50`)
- **Cards**: White dengan border gray-100 (`bg-white border border-gray-100`)
- **Text Primary**: Gray-900 (`text-gray-900`)
- **Text Secondary**: Gray-500 (`text-gray-500`)
- **Accent Colors**:
  - COBIT: Emerald (`emerald-500`)
  - PMO: Blue/Indigo (`blue-500 to indigo-600`)
  - HR: Violet/Purple (`violet-500 to purple-600`)
  - Finance: Amber/Orange (`amber-500 to orange-600`)

### Component Patterns
- **Cards**: `bg-white rounded-2xl p-6 border border-gray-100 shadow-sm`
- **Hero Section**: `bg-gradient-to-r from-emerald-500 to-teal-600 rounded-3xl`
- **Stats Badge**: `bg-{color}-100 text-{color}-700 rounded-full`
- **Hover Effects**: `hover:shadow-xl hover:scale-[1.02] transition-all duration-300`

---

## 🔑 Pola & Konvensi

### 1. Inertia Page Props
Setiap page menerima props dari Laravel controller/route:

```vue
<script setup>
// Props dari Laravel
const props = defineProps({
    user: Object,           // Data user dari auth()->user()
    apps: Array,            // Daftar SSO apps
    firebaseConfig: Object, // Config Firebase untuk login
    error: String,          // Error message (optional)
});
</script>
```

### 2. Layout Pattern
Page HARUS wrap content dengan layout:

```vue
<template>
    <AuthenticatedLayout :user="user" title="Page Title">
        <!-- Content langsung di sini, TANPA wrapper div tambahan -->
        <div class="mb-8">...</div>
    </AuthenticatedLayout>
</template>
```

⚠️ **JANGAN** tambahkan `min-h-screen`, `max-w-7xl`, atau `px-4` di page - sudah ada di Layout!

### 3. Safe Null Handling
Selalu handle null/undefined untuk data user:

```javascript
// ✅ BENAR
const displayName = computed(() => {
    return props.user?.nama || props.user?.email?.split('@')[0] || 'User';
});

// ❌ SALAH - akan error jika nama null
const displayName = props.user.nama.split(' ')[0];
```

### 4. Firebase Auth Pattern
Login menggunakan Firebase popup, lalu kirim token ke backend:

```javascript
// 1. Login dengan provider
const result = await signInWithPopup(auth, provider);

// 2. Ambil ID token
const idToken = await result.user.getIdToken();

// 3. Kirim ke backend Laravel
const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    },
    body: JSON.stringify({ id_token: idToken }),
});

// 4. Redirect jika sukses
if (response.ok) {
    window.location.href = '/dashboard';
}
```

---

## 🎨 Design System

### Color Palette (Dark Theme - Admin Standard)
```
Background Main: #0f0f0f (Ultra Dark)
Card Surface:    #1a1a1a (Dark Gray)
Border:          white/5 (Subtle)
Text Primary:    white
Text Secondary:  gray-400
Hover:           white/5 or white/10
```

### Admin Component Patterns
- **Stats Card**:
  ```html
  <div class="bg-white dark:bg-[#1a1a1a] rounded-xl p-5 border border-gray-200/80 dark:border-white/5">
  ```
- **Status Badges**:
  - Pending: `bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400`
  - Approved: `bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400`
  - Approved: `bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400`
  - Rejected: `bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400`
- **Confirmation Modal**:
  Gunakan `Components/ConfirmModal.vue` untuk aksi destruktif/penting.
  ```vue
  <ConfirmModal
      :show="showModal"
      title="Hapus User"
      message="Yakin ingin menghapus?"
      type="danger"
      @confirm="deleteUser"
      @close="showModal = false"
  />
  ```


### Common Classes
```html
<!-- Card dengan glass effect -->
<div class="bg-slate-800/50 backdrop-blur-xl rounded-2xl p-6 border border-slate-700/50">

<!-- Gradient button -->
<div class="bg-gradient-to-br from-blue-500 to-purple-600">

<!-- Icon container -->
<div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
```

### Spacing Convention
- Section gap: `mb-8` atau `mb-12`
- Card padding: `p-6`
- Grid gap: `gap-6`

---

---

## 🏗️ Architecture Patterns

### 1. Container vs Presentational (Smart vs Dumb)
Kami menggunakan pola **Container/Presentational** untuk memisahkan logic (`Pages/`) dari UI (`Partials/` atau `Components/`).

#### **Container Component (Smart)**
- **Lokasi:** `resources/js/Pages/*` (contoh: `Admin/Users/Index.vue`)
- **Tanggung Jawab:**
  - Mengambil data (Inertia Props).
  - Mengelola State lokal yang kompleks (Modal, Form).
  - Integrasi dengan Backend (Router.post/put/delete).
  - Mengirim props ke Child Components.
- **Ciri-ciri:** Penuh dengan script logic, sedikit styling HTML langsung.

#### **Presentational Component (Dumb)**
- **Lokasi:** `Partials/*` atau `Components/*` (contoh: `UserTable.vue`, `UserFilters.vue`)
- **Tanggung Jawab:**
  - Menerima data lewat `props` & menampilkannya.
  - Mengirim event ke Parent lewat `emit` (klik tombol, input changed).
  - **TIDAK BOLEH** akses API/Inertia Router langsung (kecuali navigasi link sederhana).
- **Ciri-ciri:** Fokus di `<template>`, script tipis (hanya props/emit).

### 2. Atomic Elements (Generic Components)
Komponen kecil yang *reusable* di seluruh aplikasi.
- **Contoh:** `EmptyState.vue`, `SectionHeader.vue`, `ConfirmModal.vue`.
- **Aturan:** Harus agnostik (tidak terikat pada data domain tertentu seperti "User" atau "Produk"). Gunakan Slot atau Props generik.

### 🛑 Architecture Do's & Don'ts

| Do's (✅ Lakukan) | Don'ts (❌ Hindari) |
|-------------------|---------------------|
| Pecah halaman > 300 baris menjadi `Partials/*`. | Membiarkan satu file `Index.vue` membengkak hingga 800+ baris ("God Object"). |
| Logic submit form ada di Parent (Page). | Logic submit form ada di dalam komponen tabel/filter. |
| Gunakan `emit` untuk komunikasi Child -> Parent. | Mengubah props secara langsung di Child (Vue Violation). |
| Buat komponen generik untuk UI yang berulang (Header, Empty State). | Copy-paste HTML header/empty state di setiap halaman. |

---

## 📄 Page Specifications

### Login.vue
**Path:** `/` atau `/login`
**Layout:** None (standalone)
**Features:**
- Split screen: Image carousel (left) + Form (right)
- 4 Social login buttons: Google, GitHub, Apple, Facebook
- Auto-slide carousel setiap 5 detik
- Glass morphism effects
- Responsive (carousel hidden on mobile)

**Props:**
```typescript
{
    firebaseConfig: {
        apiKey: string,
        authDomain: string,
        projectId: string,
        storageBucket: string,
        messagingSenderId: string,
        appId: string,
    },
    error?: string
}
```

### Dashboard.vue
**Path:** `/dashboard`
**Layout:** AuthenticatedLayout
**Features:**
- Welcome section dengan avatar
- Quick stats (3 cards)
- SSO Apps grid
- Help section

**Props:**
```typescript
{
    user: {
        id: number,
        nama: string | null,
        email: string,
        avatar_url: string | null,
    },
    apps: Array<{
        key: string,
        name: string,
        description: string,
        sso_url: string,
    }>
}
```

---

### Admin/Dashboard.vue
**Path:** `/admin`
**Layout:** AdminLayout
**Features:**
- Toggle Dark Mode (Global State)
- Stats Grid (4 cards)
- Lists: Pending Approvals & Recent Users
- Quick Actions
```vue
<AdminLayout title="Admin Dashboard">
    <!-- Dark Mode Ready Classes -->
    <div class="bg-white dark:bg-[#1a1a1a] border border-gray-200/80 dark:border-white/5">...</div>
</AdminLayout>
```

---

## 🔧 Build Commands

```bash
# Development dengan hot reload
npm run dev

# Production build
npm run build

# Clear Laravel config (setelah ubah .env)
php artisan config:clear
```

---

## ⚠️ JANGAN LAKUKAN

1. ❌ Jangan pakai `GuestLayout.vue` - tidak diperlukan lagi
2. ❌ Jangan hapus `resources/views/app.blade.php` - ROOT template Inertia
3. ❌ Jangan tambah wrapper `min-h-screen` di dalam page
4. ❌ Jangan akses `user.nama` langsung tanpa null check
5. ❌ Jangan buat Blade view baru - semua UI pakai Vue
6. ❌ Jangan lupa `npm run build` setelah edit Vue

---

## ✅ SELALU LAKUKAN

1. ✅ Gunakan `computed()` untuk derived state
2. ✅ Handle null dengan optional chaining (`?.`)
3. ✅ Wrap page dengan `AuthenticatedLayout` untuk halaman auth
4. ✅ Gunakan Tailwind classes yang konsisten
5. ✅ Test di mobile view (carousel hidden)
6. ✅ Clear config cache setelah ubah `.env`

---

## 🔗 Routing (Inertia)

| Route | Page Component | Layout |
|-------|---------------|--------|
| `/` | `Auth/Login.vue` | None |
| `/login` | `Auth/Login.vue` | None |
| `/dashboard` | `Dashboard.vue` | AuthenticatedLayout |

Routes didefinisikan di `routes/web.php` dengan `Inertia::render()`.

---

## 🧩 Shared Data (via Middleware)

Data yang di-share ke semua pages via `HandleInertiaRequests.php`:

```php
[
    'auth' => [
        'user' => $request->user(),  // User model atau null
    ],
    'flash' => [
        'success' => session('success'),
        'error' => session('error'),
    ],
]
```

Akses di Vue:
```javascript
import { usePage } from '@inertiajs/vue3';
const page = usePage();
const authUser = page.props.auth.user;
```

---

## 📝 Checklist Sebelum Commit

- [ ] `npm run build` sukses tanpa error
- [ ] Tidak ada console error di browser
- [ ] Responsive di mobile
- [ ] Null handling untuk semua user data
- [ ] Tidak ada duplikasi wrapper/layout

---

*Last updated: 2 Februari 2026*
