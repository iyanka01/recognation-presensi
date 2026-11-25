# LAPORAN AUDIT KEAMANAN & PENGUATAN SISTEM (SECURITY AUDIT & HARDENING REPORT)

**Aplikasi:** Mayantara Presensi  
**Target Keamanan:** OWASP ASVS 5.0 Level 2, OWASP Top 10 (2021/2025), Laravel 12 Security Best Practices  
**Status Audit:** COMPLETED & VERIFIED  
**Cabang Git:** `security-hardening`  
**Tanggal:** 25 November 2026  

---

## 1. RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)

Audit dan hardening keamanan menyeluruh telah dilakukan terhadap source code aplikasi **Mayantara Presensi** dengan fokus pada kepatuhan terhadap standar **OWASP Application Security Verification Standard (ASVS) 5.0 Level 2** dan **Laravel 12 Security Best Practices**.

Audit ini diselesaikan tanpa merusak database produksi, tanpa menghapus fitur yang berjalan, dan menjaga kompatibilitas seluruh modul operasional sekolah (Admin, Guru, Siswa, dan Wali Murid).

### Rekapitulasi Hasil Pengujian:
- **Total Celah Keamanan Teridentifikasi:** 12 Celah (3 Critical, 5 High, 4 Medium).
- **Total Celah Berhasil Diperbaiki:** 12 Celah (100% Remediated).
- **Pengujian Otomatis (Automated Tests):** 26 Passed, 0 Failed (117 Assertions).
- **Kompilasi Frontend (Vite & Tailwind):** Clean build (0 error).

---

## 2. MATRIKS CELAH KEAMANAN (VULNERABILITY AUDIT MATRIX)

| ID | Kategori OWASP | CWE | Tingkat Keparahan | Deskripsi Kerentanan | File Terdampak | Status Remediasi |
|---|---|---|---|---|---|---|
| **SEC-01** | A07: Identification & Auth Failures | CWE-798 | **CRITICAL** | Hardcoded API Token (`mayantara-migrasi-2026`) pada endpoint ekspor data sekolah. | `routes/web.php` | **FIXED** (Dipindahkan ke `.env`, validasi Bearer Token `hash_equals`) |
| **SEC-02** | A01: Broken Access Control | CWE-22 | **CRITICAL** | Storage Fallback Path Traversal pada rute `/storage/{path}` yang berpotensi membocorkan file sistem. | `routes/web.php` | **FIXED** (Validasi canonical path `realpath` & pembatasan direktori `storage/app/public`) |
| **SEC-03** | A07: Identification & Auth Failures | CWE-259 | **CRITICAL** | Endpoint dev berbahaya `/init-admin` yang mereset password administrator ke `admin123`. | `routes/web.php` | **FIXED** (Dihapus dari rute web; digantikan Artisan Command CLI `php artisan admin:init`) |
| **SEC-04** | A05: Security Misconfiguration | CWE-200 | **HIGH** | Endpoint `/cek` mengekspos versi PHP, konfigurasi database, username DB, dan status server. | `routes/web.php` | **FIXED** (Dihapus sepenuhnya dari rute web) |
| **SEC-05** | A02: Cryptographic Failures | CWE-295 | **HIGH** | `withoutVerifying()` pada panggilan HTTP tarik API backup menonaktifkan verifikasi SSL/TLS (MITM Risk). | `app/Services/Backup/BackupService.php` | **FIXED** (Verifikasi SSL diaktifkan default dengan konfigurasi `services.migration.verify_ssl`) |
| **SEC-06** | A07: Identification & Auth Failures | CWE-287 | **HIGH** | Fallback plaintext password `'siswa123'` dan bypass akun ber-password kosong pada login siswa. | `app/Http/Controllers/AuthController.php`, `SiswaAuthController.php` | **FIXED** (Diwajibkan `Hash::check()` dengan penanganan exception aman) |
| **SEC-07** | A01: Broken Access Control | CWE-352 | **HIGH** | Pengecualian CSRF pada seluruh endpoint login dan logout (`validateCsrfTokens(except: ...)`). | `bootstrap/app.php` | **FIXED** (CSRF diaktifkan penuh pada semua rute autentikasi; hanya `api/*` yang dikecualikan) |
| **SEC-08** | A07: Identification & Auth Failures | CWE-307 | **HIGH** | Ketiadaan Rate Limiting pada form login publik siswa, guru, dan admin (Brute Force Risk). | `routes/web.php`, `routes/admin.php`, `routes/siswa.php` | **FIXED** (Diterapkan `RateLimiter::for('login')` 5 percobaan/menit dengan pesan ramah pengguna) |
| **SEC-09** | A10: Server-Side Request Forgery | CWE-918 | **HIGH** | Form Tarik API Data Lama rentan terhadap SSRF menuju internal network / cloud metadata (169.254.169.254). | `app/Http/Requests/Backup/TarikApiRequest.php` | **FIXED** (Validasi host & penolakan IP privat/loopback pada production) |
| **SEC-10** | A05: Security Misconfiguration | CWE-693 | **MEDIUM** | Tidak adanya HTTP Security Headers (Clickjacking, MIME Sniffing, HSTS). | `bootstrap/app.php` | **FIXED** (Dibuat `SecurityHeadersMiddleware`: X-Frame-Options, CSP, HSTS, Permissions-Policy) |
| **SEC-11** | A09: Security Logging & Monitoring | CWE-778 | **MEDIUM** | Tidak ada Audit Log terpusat untuk mencatat event keamanan (login gagal, download backup, export data). | Database / App | **FIXED** (Dibuat tabel `security_audit_logs` & service `SecurityLogService`) |
| **SEC-12** | A05: Security Misconfiguration | CWE-552 | **MEDIUM** | Konfigurasi `'serve' => true` pada disk `'local'` (`app/private`) di `filesystems.php`. | `config/filesystems.php` | **FIXED** (Diubah `'serve' => false` untuk mencegah ekspos berkas privat) |

---

## 3. RINCIAN PERBAIKAN & PENGUATAN (HARDENING IMPLEMENTATION)

### 3.1. Autentikasi & Pengelolaan Kredensial
1. **Pemberantasan Password Bypass:**
   - Menghilangkan logika fallback `if ($passwordInput === 'siswa123' && ...)` pada [AuthController.php](file:///e:/jibas/mayantara-presensi/app/Http/Controllers/AuthController.php) dan [SiswaAuthController.php](file:///e:/jibas/mayantara-presensi/app/Http/Controllers/SiswaAuthController.php).
   - Mewajibkan hashing `Hash::check()` untuk seluruh user. Password null atau kosong otomatis ditolak.
   - Pengecekan password dibungkus blok `try-catch` sehingga jika ada string invalid di database, sistem tidak melempar HTTP 500 melainkan menolak autentikasi secara aman.
2. **Penghapusan Route Reset Darurat via Web:**
   - Menghapus route `/init-admin` dan `/cek` dari [routes/web.php](file:///e:/jibas/mayantara-presensi/routes/web.php).
   - Menyediakan perintah artisan CLI yang aman di [app/Console/Commands/InitAdminCommand.php](file:///e:/jibas/mayantara-presensi/app/Console/Commands/InitAdminCommand.php) (`php artisan admin:init --username=admin`) dengan input password interaktif terenkripsi.

### 3.2. Rate Limiting & Perlindungan Brute Force
- Dikonfigurasikan pembatas laju percobaan login terpusat di [AppServiceProvider.php](file:///e:/jibas/mayantara-presensi/app/Providers/AppServiceProvider.php):
  - Maksimal 5 percobaan per menit per kombinasi username dan IP address.
  - Respon yang dikembalikan dialihkan kembali ke form dengan pesan ramah pengguna tanpa merusak UI.
- Diterapkan middleware `throttle:login` pada:
  - `POST /login` (Portal Guru & Siswa)
  - `POST /admin/login` (Panel Administrator)
  - `POST /siswa/login` (Portal Siswa / Wali Murid)

### 3.3. Proteksi CSRF & Manajemen Sesi
- Mengaktifkan kembali token CSRF pada seluruh endpoint autentikasi di [bootstrap/app.php](file:///e:/jibas/mayantara-presensi/bootstrap/app.php) dengan menghapus `'login'`, `'logout'`, `'admin/login'`, dll dari daftar `except`.
- Hanya route API stateless (`api/*`) yang dikecualikan dari verifikasi CSRF.
- Penanganan exception HTTP 419 (Token Mismatch / Page Expired) tetap aktif untuk mengarahkan pengguna kembali ke halaman login dengan notifikasi yang informatif.
- Rute logout diperkuat hanya menerima metode `POST` dengan token CSRF valid. Akses langsung via `GET` dialihkan ke login tanpa memutus sesi (mencegah Logout CSRF via `<img>` injection).

### 3.4. Keamanan API & Pencegahan Data Leakage
- Rute `/api/export-migrasi-sekolah`:
  - Token API disimpan di environment variable `MIGRATION_API_TOKEN`.
  - Verifikasi token menggunakan fungsi konstan waktu `hash_equals()` untuk mencegah timing-attack.
  - Mendukung standar header `Authorization: Bearer <token>`.
  - Diterapkan `makeHidden(['password', 'remember_token'])` pada model Guru dan Siswa sebelum diekspor.
  - Diterapkan rate limiter `throttle:10,1`.

### 3.5. Pencegahan Path Traversal & Konfigurasi Berkas
- Fallback route `/storage/{path}` pada [routes/web.php](file:///e:/jibas/mayantara-presensi/routes/web.php):
  - Menggunakan fungsi kanonikal `realpath()` untuk memverifikasi lokasi berkas.
  - Memastikan path yang diakses harus diawali dengan prefix path direktori publik `storage/app/public`.
  - Up-directory traversal (`../`, `..\`) otomatis menghasilkan status 404/403.
- Disk `'local'` (`app/private`) di [config/filesystems.php](file:///e:/jibas/mayantara-presensi/config/filesystems.php):
  - Mengubah `'serve' => false` agar berkas privat sistem tidak dapat disajikan sembarangan melalui route HTTP.

### 3.6. Pencegahan SSRF (Server-Side Request Forgery)
- Pada [app/Http/Requests/Backup/TarikApiRequest.php](file:///e:/jibas/mayantara-presensi/app/Http/Requests/Backup/TarikApiRequest.php):
  - URL yang dimasukkan divalidasi skema protokolnya (`http` atau `https`).
  - Resolusi DNS host diperiksa terhadap rentang IP privat RFC 1918 (10.0.0.0/8, 172.16.0.0/12, 192.168.0.0/16, 127.0.0.0/8) serta IP metadata cloud (169.254.169.254). Permintaan ke IP privat langsung ditolak.

### 3.7. HTTP Security Headers
Dibuat middleware global [app/Http/Middleware/SecurityHeadersMiddleware.php](file:///e:/jibas/mayantara-presensi/app/Http/Middleware/SecurityHeadersMiddleware.php) yang menyisipkan header kepatuhan OWASP ASVS:
- `X-Frame-Options: SAMEORIGIN` (Mencegah UI Redressing / Clickjacking)
- `X-Content-Type-Options: nosniff` (Mencegah eksekusi file berbasis MIME Sniffing)
- `Referrer-Policy: strict-origin-when-cross-origin` (Melindungi URL sensitif saat bernavigasi ke domain luar)
- `Permissions-Policy: camera=(self), geolocation=(self), microphone=(), payment=()` (Hanya mengizinkan kamera dan GPS untuk fitur presensi)
- `X-XSS-Protection: 1; mode=block`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains` (Diaktifkan otomatis saat request berjalan di atas HTTPS)

### 3.8. Sistem Security Audit Trail & Logging
- Dibuat migrasi dan tabel [database/migrations/2026_11_25_190000_create_security_audit_logs_table.php](file:///e:/jibas/mayantara-presensi/database/migrations/2026_11_25_190000_create_security_audit_logs_table.php).
- Dibuat model [app/Models/SecurityAuditLog.php](file:///e:/jibas/mayantara-presensi/app/Models/SecurityAuditLog.php) dan service terpusat [app/Services/Security/SecurityLogService.php](file:///e:/jibas/mayantara-presensi/app/Services/Security/SecurityLogService.php).
- Mencatat secara otomatis event kritis:
  - `auth.login.success` & `auth.login.failed` (Lengkap dengan IP, User-Agent, user_type, user_id).
  - `auth.logout`
  - `backup.create`, `backup.download`, `backup.delete`, `backup.restore`
  - `migration.pull` & `migration.export`
- Seluruh detail audit log disanitasi agar kredensial (password, token rahasia) tidak pernah tersimpan di tabel log.

---

## 4. BUKTI VERIFIKASI & PENGUJIAN (TESTING PROOF)

### 4.1. Hasil Pengujian Unit & Feature (PHPUnit)
Perintah dijalankan:
```bash
php artisan test
```
Hasil:
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\ExampleTest
  ✓ the root url redirects to login

   PASS  Tests\Feature\PresensiMapelTest
  ✓ guru dapat login dan melihat dashboard guru
  ✓ guru nonaktif tidak dapat login
  ✓ admin dapat login dan melihat dashboard admin
  ✓ guru dapat logout
  ✓ admin dapat logout
  ✓ guest diarahkan ke halaman login
  ✓ guru tidak bisa mengakses menu admin
  ✓ admin bisa crud mata pelajaran
  ✓ mapel yang dipakai jadwal tidak bisa dihapus
  ✓ admin bisa membuat jadwal
  ✓ jadwal bentrok ditolak
  ✓ guru bisa input absen mata pelajaran
  ✓ input absen ditolak tanpa data pembelajaran
  ✓ absensi bisa diubah tanpa duplikat
  ✓ guru tidak bisa akses jadwal guru lain
  ✓ rekap absensi mapel tampil untuk admin
  ✓ guru baru ber nip otomatis punya akun login

   PASS  Tests\Feature\SecurityHardeningTest
  ✓ security headers are present on all responses
  ✓ dev routes init admin and cek are not accessible
  ✓ storage fallback rejects path traversal
  ✓ migration api requires valid bearer token
  ✓ plaintext password fallback is completely rejected
  ✓ login success and failure are recorded in security audit logs
  ✓ direct get logout does not log out user

  Tests:    26 passed (117 assertions)
  Duration: 1.88s
```

### 4.2. Hasil Kompilasi Frontend
Perintah dijalankan:
```bash
npm run build
```
Hasil:
```text
vite v7.3.5 building client environment for production...
transforming...
✓ 59 modules transformed.
rendering chunks...
public/build/manifest.json              0.33 kB │ gzip:  0.17 kB
public/build/assets/app-Bp_EJAsk.css  207.39 kB │ gzip: 28.58 kB
public/build/assets/app-D8h7K1Pf.js    91.09 kB │ gzip: 33.70 kB
✓ built in 3.16s
```

---

## 5. PANDUAN DEPLOYMENT KE PRODUKSI (PRODUCTION SECURITY CHECKLIST)

Sebelum merilis perubahan ke server produksi (Cloud / VPS / Railway), pastikan variabel lingkungan berikut telah terisi dengan benar di berkas `.env` server:

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://presensi.domain-anda.sch.id

# Token rahasia khusus untuk sinkronisasi/migrasi API antar server
MIGRATION_API_TOKEN=generate_string_acak_minimal_32_karakter
MIGRATION_VERIFY_SSL=true

# Pastikan session diamankan di atas HTTPS
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

Perintah migrasi di server produksi (Aman, hanya membuat tabel baru `security_audit_logs` tanpa menyentuh data lama):
```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Jika perlu membuat atau mereset akun administrator di server produksi:
```bash
php artisan admin:init --username=admin
```
