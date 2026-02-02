# Deployment Checklist - Divusi SSO ke Laravel Cloud

## Pre-Deployment (Local)

### Code & Repository
- [ ] Semua fitur sudah tested di local
- [ ] Git commit dan push ke `origin/main`
- [ ] Tidak ada sensitive data di repository (credentials, keys)
- [ ] `.gitignore` mencakup: `.env`, `/storage/app/firebase/`, `/vendor/`, `/node_modules/`
- [ ] `README.md` sudah update dengan instruksi setup
- [ ] `LARAVEL_CLOUD_DEPLOYMENT.md` sudah siap sebagai referensi

### Application Configuration
- [ ] `composer.json` sudah update dengan semua dependencies
- [ ] `package.json` sudah update dengan Vite & Tailwind (jika digunakan)
- [ ] `phpunit.xml` dikonfigurasi untuk testing
- [ ] `config/firebase.php` sudah siap (menggunakan `storage_path()`)
- [ ] `config/app.php` - APP_NAME dan timezone sudah benar
- [ ] `config/database.php` - MySQL connection sudah configured
- [ ] `.env.example` sudah up-to-date dengan semua required vars

### Firebase Preparation
- [ ] Firebase project sudah created
- [ ] Service Account JSON sudah downloaded
- [ ] Service Account JSON sudah aman (di local backup, bukan di repo)
- [ ] Firebase Console → Authentication → Google provider enabled
- [ ] Firebase Console → Authorized domains → tambahkan production domain
- [ ] Firebase Console → API keys sudah di-copy

### Database Migrations
- [ ] Semua migrations sudah dibuat dan tested locally
- [ ] Migrations include: users, cache, jobs, personal_access_tokens, etc.
- [ ] `database/factories/` sudah siap untuk seeding (jika perlu)
- [ ] `database/seeders/` sudah configured

### Frontend
- [ ] `resources/views/auth/login.blade.php` sudah using inline Firebase CDN
- [ ] `resources/views/dashboard.blade.php` sudah siap
- [ ] `resources/css/app.css` dan `resources/js/` sudah complete
- [ ] Vite config (`vite.config.js`) sudah verified

### Session & Auth
- [ ] `config/session.php` set ke `SESSION_DRIVER=database`
- [ ] `config/auth.php` sudah configure Guard dan Provider
- [ ] `app/Http/Middleware/` sudah verified
- [ ] `routes/api.php` dan `routes/web.php` sudah correct
- [ ] CSRF middleware sudah exclude API routes jika perlu

---

## Laravel Cloud Setup

### 1. Account & Project Creation
- [ ] Sudah register di https://laravel.cloud
- [ ] Sudah login dengan GitHub account
- [ ] Sudah buat project baru di Laravel Cloud dashboard
- [ ] GitHub repo `divusi-sso` sudah ter-connect

### 2. Environment Variables
- [ ] Semua variables dari `.env.example` sudah di-set di Laravel Cloud dashboard
- [ ] **Critical variables:**
  - [ ] `APP_KEY` (generate: `php artisan key:generate` locally)
  - [ ] `APP_URL` (domain production, e.g., `https://sso.divusi.com`)
  - [ ] `FIREBASE_*` variables dari Firebase Console
  - [ ] `FIREBASE_SERVICE_ACCOUNT_JSON` (full JSON content)
  - [ ] Database credentials (Laravel Cloud auto-provides)
  - [ ] `REDIS_HOST`, `REDIS_PASSWORD` (Laravel Cloud auto-provides)
  - [ ] `SESSION_DRIVER=database`
  - [ ] `CACHE_DRIVER=redis`
  - [ ] `QUEUE_CONNECTION=redis`

### 3. Domain & SSL
- [ ] Custom domain sudah ditambahkan ke Laravel Cloud
- [ ] DNS records sudah di-update sesuai instruksi
- [ ] SSL certificate pending atau sudah installed
- [ ] Domain sudah resolvable: `ping sso.divusi.com`

### 4. Database Setup
- [ ] MySQL database sudah di-create di Laravel Cloud
- [ ] Database credentials sudah di-set di environment variables
- [ ] Database character set: `utf8mb4`
- [ ] Database collation: `utf8mb4_unicode_ci`

### 5. Redis Configuration
- [ ] Redis instance sudah di-provision (auto dari Laravel Cloud)
- [ ] `REDIS_HOST` dan `REDIS_PASSWORD` sudah di-set

### 6. Build & Deployment Configuration
- [ ] Build script verified:
  ```bash
  composer install --optimize-autoloader --no-dev
  npm install && npm run build
  ```
- [ ] Post-deployment commands configured:
  ```bash
  php artisan migrate --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] Deployment timeout set ke minimal 10 menit

---

## Deployment Execution

### Deploy
- [ ] Klik **Deploy** di Laravel Cloud dashboard
- [ ] Monitor build progress (2-3 menit)
- [ ] Monitor migrations (1-2 menit)
- [ ] Tunggu status **Active**

### Post-Deployment Verification
- [ ] Application accessible: `curl https://sso.divusi.com`
- [ ] Login page loads: `https://sso.divusi.com/login`
- [ ] Google OAuth flow works (test login)
- [ ] User data stored di database
- [ ] Session maintained after login
- [ ] Dashboard accessible: `https://sso.divusi.com/dashboard`

### Application Health
- [ ] Check logs di Laravel Cloud dashboard
- [ ] No errors di `storage/logs/laravel.log`
- [ ] Database connections working
- [ ] Redis connection working
- [ ] Firebase authentication working

---

## Post-Deployment (Production)

### Security Hardening
- [ ] `APP_DEBUG=false` di production
- [ ] `FILESYSTEM_DISK` set ke `public` atau `s3` (jika file uploads)
- [ ] CORS headers configured jika ada cross-domain requests
- [ ] Rate limiting enabled di `app/Http/Middleware/`
- [ ] Session timeout appropriate (default 120 menit)

### Firebase Console Updates
- [ ] Authorized domains di Firebase Console include production domain
- [ ] OAuth consent screen configured (if required)
- [ ] Test OAuth flow dari production domain

### Monitoring & Logging
- [ ] Enable application monitoring di Laravel Cloud
- [ ] Setup email alerts untuk errors
- [ ] Monitor database performance
- [ ] Monitor Redis usage
- [ ] Setup backup automation

### Team Collaboration
- [ ] Team members added ke Laravel Cloud project
- [ ] Deployment permissions configured
- [ ] Documentation shared dengan team

### SSO Integration (For Other Apps)
- [ ] COBIT, PMO, dan apps lain sudah update redirect URLs
- [ ] Callback URLs di other apps point ke: `https://sso.divusi.com/sso/redirect/{app}`
- [ ] Integration tested end-to-end

---

## Troubleshooting & Rollback

### If Deployment Failed
- [ ] Check build logs di Laravel Cloud dashboard
- [ ] Common issues:
  - [ ] PHP version mismatch
  - [ ] Dependency conflicts (`composer install`)
  - [ ] Node version mismatch (for Vite builds)
  - [ ] Environment variable missing

### If Application Has Errors
- [ ] Check `storage/logs/laravel.log` via Laravel Cloud dashboard
- [ ] SSH to production instance (if available) untuk debug
- [ ] Check database migrations: `php artisan migrate:status`
- [ ] Verify Firebase credentials

### Rollback to Previous Version
- [ ] Go to Laravel Cloud dashboard → Deployments
- [ ] Select previous successful deployment
- [ ] Click **Rollback**
- [ ] Confirm

---

## Ongoing Maintenance

### Updates
- [ ] Plan regular security updates untuk dependencies
- [ ] Monitor Laravel security releases
- [ ] Test updates locally sebelum production deploy
- [ ] Enable auto-deployment untuk non-breaking updates (optional)

### Backups
- [ ] Laravel Cloud auto-backup database (verify di settings)
- [ ] Backup storage files jika ada file uploads
- [ ] Test backup restore periodically

### Scaling
- [ ] Monitor application performance
- [ ] If high traffic, upgrade workers atau add replicas
- [ ] If database slow, optimize queries atau upgrade plan
- [ ] If Redis slow, increase memory atau upgrade plan

### Documentation
- [ ] Keep deployment docs updated
- [ ] Document any custom configurations
- [ ] Keep runbooks untuk common issues
- [ ] Share knowledge dengan team

---

## Done!

After completing all items, your Divusi SSO application is ready for production on Laravel Cloud!

**Additional Resources:**
- Laravel Cloud Documentation: https://docs.laravel.cloud
- Laravel Documentation: https://laravel.com/docs
- Firebase Documentation: https://firebase.google.com/docs
