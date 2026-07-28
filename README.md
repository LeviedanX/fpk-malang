# FPK Kota Malang

Website resmi Forum Pembauran Kebangsaan Kota Malang — Laravel 12, MySQL,
Vite, Tailwind, Alpine.js.

---

## Pengembangan lokal

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build

composer dev   # server + log + Vite, tiga proses sekaligus
```

`composer dev` menjalankan `php artisan serve`, `php artisan pail`, dan
`npm run dev` bersamaan lewat `scripts/dev.php` — skrip itu juga yang
mengaktifkan OPcache untuk sesi pengembangan (lihat `php-ini/opcache.ini`).

```bash
composer test   # php artisan test, terkunci ke database *_test
```

Admin default dari seeder: `admin@gmail.com` / `admin123` — ganti sebelum
hosting.

---

## Deployment

### Prasyarat server

- PHP 8.3+ beserta ekstensi PDO MySQL, mbstring, DOM, Fileinfo, Intl, GD/WebP,
  dan **OPcache aktif**; `expose_php=Off`.
- MySQL dengan database dan user khusus aplikasi (bukan root).
- Document root mengarah ke direktori `public`, bukan root proyek.
- HTTPS aktif sebelum aplikasi menerima trafik.

Privilege minimum; ganti nama database, user, host, dan password lewat panel
hosting — jangan simpan password asli di repository:

```sql
CREATE USER 'fpk_app'@'localhost' IDENTIFIED BY '<PASSWORD-KUAT>';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP
ON fpk_malang.* TO 'fpk_app'@'localhost';
FLUSH PRIVILEGES;
```

Gunakan database terpisah untuk test. Jangan menjalankan PHPUnit pada database
produksi.

### Reverse proxy dan CDN

Bila aplikasi berada di belakang reverse proxy, load balancer, atau CDN
(Cloudflare, nginx di depan PHP-FPM, dsb.), **`TRUSTED_PROXIES` wajib diisi**
di `.env` — daftar IP/CIDR proxy dipisahkan koma, atau `*` bila seluruh trafik
dijamin hanya melewati proxy tersebut.

Jika dibiarkan kosong padahal ada proxy di depan aplikasi:

- setiap pengunjung terlihat memakai satu IP yang sama, sehingga rate limit
  login runtuh menjadi satu bucket dan satu penyerang dapat mengunci semua admin;
- log aktivitas admin mencatat IP proxy, bukan IP pelaku;
- request tidak terdeteksi sebagai HTTPS, sehingga header HSTS tidak terkirim.

`php artisan deploy:check` memberi peringatan bila nilai ini kosong.

### Instalasi baru

1. Siapkan `.env` di server dan isi seluruh nilai produksi (lihat header di
   `.env` proyek ini untuk daftar lengkap kunci yang wajib diisi).
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan key:generate` — hanya untuk instalasi baru.
4. `php artisan migrate --force`
5. Buat administrator: `php artisan admin:create`, atau sekali saja
   `php artisan admin:create --from-env`, lalu hapus `ADMIN_PASSWORD` dari
   environment.
6. `npm ci && npm run build` di build host.
7. Pastikan `public/hot` tidak ada.
8. `php artisan storage:link`
9. `php artisan optimize`
10. `php artisan deploy:check`

Project tidak menggunakan background job, sehingga `.env` produksi memakai
`QUEUE_CONNECTION=sync` dan tidak membutuhkan queue worker.

Untuk restore/update, pertahankan `APP_KEY` lama agar data terenkripsi tetap
dapat dibaca.

### Pengerasan direktori unggahan pada nginx

Lampiran chat berasal dari tamu anonim. Validasi menolak berkas non-gambar dan
gambar yang lolos disandikan ulang menjadi WebP, sehingga berkas yang bisa
dieksekusi semestinya tidak pernah mendarat di sana. Lapis terakhirnya ada pada
`public/.htaccess` dan `storage/app/public/.htaccess` — dan **keduanya hanya
dibaca Apache**. Pada nginx, salin padanannya ke server block:

```nginx
# Berkas di /storage dilayani langsung nginx, jadi header keamanan dari PHP
# tidak berlaku di sana. Dua blok berikut yang menggantikannya.
location ^~ /storage/ {
    add_header X-Content-Type-Options "nosniff" always;
    add_header Content-Security-Policy "default-src 'none'; img-src 'self'; media-src 'self'; sandbox" always;

    # Tidak ada berkas unggahan yang boleh sampai ke PHP-FPM.
    location ~ \.(?:php|phar|phtml|pht|php[0-9]|cgi|pl|py|rb|sh)$ {
        deny all;
    }
}
```

Tanpa blok `deny` di atas, aturan `location ~ \.php$` yang lazim dipakai untuk
meneruskan permintaan ke PHP-FPM juga akan berlaku bagi berkas di `/storage`.

### Scheduler, health, dan backup

Tambahkan cron setiap menit:

```text
* * * * * cd /path/aplikasi && php artisan schedule:run >> /dev/null 2>&1
```

- `/up` adalah liveness check Laravel.
- `/ready` memeriksa koneksi database dan akses storage tanpa membocorkan error.
- Backup harian wajib mencakup dump MySQL dan `storage/app/public`.
- Enkripsi backup, simpan salinan di lokasi terpisah, dan tetapkan retensi.
- Uji restore ke lingkungan nonproduksi secara berkala; backup yang belum pernah
  direstore belum dapat dianggap tervalidasi.

### Urutan update dan rollback

1. Backup database serta upload.
2. Aktifkan maintenance mode.
3. Deploy source/build baru tanpa `.env`, `.git`, `node_modules`, test, atau `public/hot`.
4. Jalankan migration, storage link, optimize, dan deploy check.
5. Matikan maintenance mode lalu smoke-test `/`, `/artikel`, `/admin/login`, `/up`, dan `/ready`.
6. Jika gagal, kembalikan release sebelumnya dan restore database hanya jika
   migration tidak backward-compatible.

### Artefak handoff (paket rilis)

Jalankan `powershell -ExecutionPolicy Bypass -File scripts/build-release.ps1`.
Hasil berada di direktori `DEPLOY`:

- `fpk-malang-production.zip` — kode runtime, dependency Composer production,
  dan asset Vite; tidak berisi `.env`, Git, test, `node_modules`, `public/hot`,
  symlink Windows, atau upload runtime.
- `fpk-malang-uploads.zip` — isi `storage/app/public` saat build. Ekstrak ke
  `storage/app/public` di server, lalu jalankan `php artisan storage:link`.
- `README.md` — dokumen ini, ikut disertakan sebagai runbook.
- `audit-release.ps1` — audit ulang checksum, isi ZIP, SQL, env, dan secret.
- `CHECKSUMS.sha256` — checksum artefak setelah seluruh file final.

Pada Windows, verifikasi ulang handoff dengan
`powershell -ExecutionPolicy Bypass -File DEPLOY/audit-release.ps1`.

Database adalah artefak terpisah. Untuk fresh install, jalankan migration lalu
impor `fpk-malang-public-data.sql` sebelum membuat admin. File SQL publik tidak
boleh berisi tabel user, session, token, cache, log admin, atau credential.

---

## Gerbang sebelum live

Checklist ini WAJIB diverifikasi pada hosting nyata — bukan diklaim lulus dari
komputer pengembangan.

### Paket lokal (sebelum diunggah)

- [ ] Source release dibangun dari working tree terbaru, bukan artefak lama.
- [ ] Asset Vite production tersedia di `public/build`.
- [ ] Dependency Composer production dipaketkan tanpa dependency development.
- [ ] `.env`, Git, `node_modules`, test, log/cache runtime, `public/hot`, dan
  `public/storage` tidak dimasukkan ke ZIP produksi.
- [ ] Dump SQL hanya berisi data publik yang diperlukan; tabel autentikasi,
  session, token, cache, job, dan log admin tidak disertakan.
- [ ] Upload runtime dipisahkan ke `fpk-malang-uploads.zip`.
- [ ] SHA-256 dibuat setelah semua artefak final.
- [ ] Test aplikasi, formatter, build, audit dependency, pemeriksaan migrasi,
  audit secret, dan pemeriksaan isi paket lulus.
- [ ] Smoke test browser publik/admin memakai asset build, tidak ada error
  console atau horizontal overflow.

### Server tujuan

- [ ] PHP 8.3+ serta ekstensi PDO MySQL, mbstring, DOM, Fileinfo, Intl, GD/WebP,
  dan OPcache aktif; `expose_php=Off`.
- [ ] Document root mengarah ke folder `public`.
- [ ] HTTPS/domain resmi aktif dan `TRUSTED_PROXIES` terisi bila ada reverse
  proxy/CDN; log aktivitas admin terbukti mencatat IP asli, bukan IP proxy.
- [ ] `.env` production di root aplikasi telah diisi dengan `APP_KEY` dan
  credential database nyata; file tidak berada di folder publik,
  `APP_DEBUG=false`.
- [ ] Database dan user khusus aplikasi memakai least privilege, bukan root/admin.
- [ ] Migration, import `fpk-malang-public-data.sql`, dan ekstraksi upload selesai.
- [ ] `php artisan storage:link`, `php artisan optimize`, serta
  `php artisan deploy:check` lulus di server.
- [ ] Administrator production dibuat dan `ADMIN_PASSWORD` dihapus dari environment.
- [ ] Cron `schedule:run` aktif setiap menit.
- [ ] Backup database dan upload berhasil, terenkripsi, memiliki retensi, dan
  restore telah diuji di nonproduksi.
- [ ] Smoke test domain asli untuk halaman publik, asset/upload, login/logout,
  `/up`, `/ready`, header CSP, dan HSTS lulus.
- [ ] Nama, foto, jabatan, periode kepengurusan, dan dasar hukum konten telah
  disetujui sumber resmi/pemilik konten (lihat `database/seeders/FpkProfileSeeder.php`
  dan `database/seeders/ManagementSeeder.php` — data draf butuh verifikasi
  sebelum periode diaktifkan).

Paket boleh disebut **siap diunggah** hanya setelah bagian "Paket lokal" lulus.
Website boleh disebut **siap dipublikasikan/live** hanya setelah seluruh bagian
"Server tujuan" tercentang pada server tujuan.

---

## Keamanan

- `App\Support\ProfanityFilter` menyaring umpatan, ujaran SARA, ancaman, dan
  konten seksual pada chat tamu — konfigurasi di `config/profanity.php`.
- `App\Support\HtmlSanitizer` membersihkan isi artikel sebelum disimpan.
- Unggahan gambar divalidasi lalu disandikan ulang ke WebP (`ImageStorage`),
  menetralkan berkas polyglot.
- Header keamanan (CSP, nosniff, X-Frame-Options) dipasang oleh
  `App\Http\Middleware\SecurityHeaders`.
- Corpus uji serangan (XSS, unggahan berbahaya, SQLi, SSRF, dsb.) ada di
  `VIRTEST/` — direktori lokal, tidak ikut ter-commit maupun ter-deploy.
