# 🚀 Deployment Guide - cPanel

Dokumen ini menjelaskan cara men-deploy aplikasi Divusi SSO ke cPanel menggunakan GitHub Actions.

## 📋 Prasyarat

1.  Akses cPanel (FTP Account & Database Access).
2.  Akses Repository GitHub.
3.  Domain sudah terarah ke hosting.

---

## ⚙️ Setup GitHub Secrets

Agar deployment aman, jangan pernah simpan password di code. Gunakan **GitHub Secrets**.

1.  Buka Repository di GitHub.
2.  Masuk ke **Settings** > **Secrets and variables** > **Actions**.
3.  Klik **New repository secret**.
4.  Tambahkan secrets berikut:

| Secret Name | Value | Contoh |
| :--- | :--- | :--- |
| `FTP_SERVER` | Hostname/IP FTP Server | `ftp.divusi.com` atau `192.168.1.1` |
| `FTP_USERNAME` | Username FTP | `deploy@divusi.com` |
| `FTP_PASSWORD` | Password FTP | `Rahasia123!` |

---

## 🛠️ Langkah Manual (Pertama Kali)

Karena GitHub Actions hanya mengupload file, beberapa setup perlu dilakukan manual di awal.

### 1. Setup Database
1.  Buka **MySQL Database Wizard** di cPanel.
2.  Buat Database baru (misal: `divusi_sso`).
3.  Buat User Database baru.
4.  Berikan akses User ke Database (All Privileges).

### 2. Upload `.env`
File `.env` tidak boleh di-upload ke GitHub.
1.  Buka **File Manager** di cPanel.
2.  Upload file `.env` lokal Anda ke folder project (misal `public_html`).
3.  Edit `.env` di cPanel dan sesuaikan config database:
    ```env
    APP_ENV=production
    APP_DEBUG=false
    APP_URL=https://sso.divusi.com

    DB_DATABASE=divusi_sso
    DB_USERNAME=divusi_user
    DB_PASSWORD=password_db_cpanel
    ```

### 3. Setup Permissions
Pastikan folder `storage` dan `bootstrap/cache` bisa ditulis (writeable).
-   Set permission `755` atau `775` (jika perlu) untuk folder:
    -   `storage/*`
    -   `bootstrap/cache`

### 4. Setup Domain (Public Folder)
Laravel menggunakan folder `public/` sebagai root. Di cPanel shared hosting, ini seringkali tricky.
**Opsi A (Recommended - Subdomain/Addon Domain):**
Saat membuat domain/subdomain, arahkan "Document Root" langsung ke `/public_html/public`.

**Opsi B (.htaccess Redirect):**
Jika tidak bisa mengubah Document Root, buat file `.htaccess` di root `/public_html/` untuk redirect ke folder public.

---

## 🚀 Cara Deploy

Setelah setup selesai:
1.  **Commit & Push** perubahan ke branch `main`.
2.  Buka tab **Actions** di GitHub untuk melihat progress deployment.
3.  Jiko sukses centang hijau ✅, file akan otomatis ter-upload ke cPanel.

---

## 🔄 Post-Deployment (Migrasi Database)

GitHub Actions di atas **tidak menjalankan** `php artisan migrate` di server cPanel karena keterbatasan akses shell (biasanya).

**Cara Menjalankan Migrasi:**

**Opsi 1: SSH (Jika Ada Akses)**
Masuk via Terminal/Putty, lalu jalankan:
```bash
cd /path/to/public_html
php artisan migrate --force
```

**Opsi 2: Route Migrasi (Darurat)**
Buat route sementara di `routes/web.php` (lalu hapus setelah dipakai):
```php
Route::get('/migrate', function () {
    \Artisan::call('migrate --force');
    return 'Migrated!';
});
```
Akses `https://domain.com/migrate` sekali, lalu **HAPUS KEMBALI** route ini demi keamanan.

---

## ⚠️ Troubleshooting

**Q: Error 500 setelah deploy?**
A: Cek file `.env`, pastikan DB credential benar. Cek folder `storage/logs/laravel.log` via File Manager.

**Q: CSS/JS tidak muncul (Vite)?**
A: Pastikan `npm run build` sukses di GitHub Actions (sudah ada di workflow). Pastikan folder `public/build` ter-upload.

**Q: Login Session hilang terus?**
A: Pastikan permission folder `storage/framework/sessions` writeable.
