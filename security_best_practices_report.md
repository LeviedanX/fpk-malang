# Audit dan Hardening Keamanan FPK Kota Malang

Tanggal audit: 26 Juli 2026
Lingkup: aplikasi Laravel, autentikasi/session admin, route dan otorisasi, input dan upload, output HTML/JavaScript, header HTTP, konfigurasi produksi, serta dependency Composer/npm.

## Ringkasan Eksekutif

Audit awal tidak menemukan backdoor, eksekusi perintah sistem, upload file executable, penyimpanan token autentikasi di browser, atau aliran DOM-XSS langsung. Kontrol dasar Laravel seperti CSRF, middleware `auth`, regenerasi session saat login, invalidasi saat logout, rate limit login, validasi upload, nama file acak, dan sanitasi rich-text sudah tersedia.

Enam area hardening masih perlu diperbaiki. Tidak ada temuan kritis yang terbukti dapat dieksploitasi tanpa prasyarat, tetapi SEC-01 dan SEC-02 berdampak tinggi karena memperbesar dampak XSS/clickjacking atau pencurian cookie session. Audit ini adalah review kode dan runtime lokal, bukan penetration test infrastruktur hosting.

## Temuan Prioritas

### SEC-01 — High — Header keamanan dan CSP belum tersedia

- Status: **Selesai**
- Lokasi: `bootstrap/app.php`, `public/.htaccess`, seluruh respons web.
- Bukti: pemeriksaan runtime tidak menemukan `Content-Security-Policy`, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, atau `Cross-Origin-Opener-Policy`.
- Dampak: tidak ada pertahanan berlapis terhadap clickjacking, pemuatan script asing, MIME sniffing, dan kebocoran referrer jika validasi/output utama suatu saat gagal.
- Perbaikan: middleware header terpusat, CSP berbasis nonce, pembatasan frame/form/base URI, serta `no-store` untuk halaman autentikasi dan admin.
- Hasil: `SecurityHeaders` aktif secara global; header runtime dan nonce terverifikasi. Apache juga memberi header dasar untuk file statis.

### SEC-02 — High — Proteksi anti-session-hijacking belum berlapis

- Status: **Selesai**
- Lokasi: `app/Http/Controllers/Auth/AuthenticatedSessionController.php:19`, `config/session.php:35`.
- Bukti: session memang diregenerasi saat login, tetapi belum memiliki fingerprint perangkat, batas umur absolut, rotasi periodik, atau kebijakan single-login.
- Dampak: cookie session yang berhasil dicuri dapat tetap digunakan sampai session kedaluwarsa atau pengguna logout.
- Perbaikan: fingerprint User-Agent, batas umur absolut, rotasi ID session periodik, satu session admin aktif, cookie terenkripsi/ketat pada produksi, dan pengujian regresi pembajakan.
- Hasil: fingerprint aktif, umur absolut 8 jam, rotasi 15 menit, single-login aktif, dan session yang berpindah fingerprint dihentikan.

### SEC-03 — High — Perubahan kredensial tidak mencabut session lama

- Status: **Selesai**
- Lokasi: `app/Http/Controllers/Admin/AdminAccountController.php:18-37`.
- Bukti: perubahan email/password hanya menyimpan model; session lain milik pengguna yang sama tidak dihapus dan session aktif tidak diputar ulang.
- Dampak: session yang sebelumnya dicuri tetap aktif walaupun administrator mengganti password.
- Perbaikan: cabut seluruh session lain dan rotasi session aktif setelah perubahan profil/password; wajibkan password saat ini untuk perubahan identitas akun.
- Hasil: perubahan profil/password mencabut session lain, merotasi session aktif, dan perubahan identitas mewajibkan password saat ini.

### SEC-04 — Medium — Dependency Guzzle memiliki empat advisory

- Status: **Selesai**
- Lokasi: `composer.lock`, `guzzlehttp/guzzle` 7.14.1.
- Bukti: `composer audit --locked` melaporkan empat advisory medium; seluruhnya diperbaiki pada versi 7.15.1 atau lebih baru.
- Dampak: potensi kebocoran fragment/referrer, scope cookie host yang keliru, DoS cookie response, dan kebocoran `Proxy-Authorization` pada penggunaan HTTP client tertentu.
- Perbaikan: perbarui Guzzle beserta lockfile, lalu ulangi audit dependency.
- Hasil: Guzzle diperbarui ke 7.15.1; `composer audit --locked` tidak menemukan advisory.

### SEC-05 — Medium — Konfigurasi produksi belum aman-by-default

- Status: **Selesai**
- Lokasi: `.env.example:2-26`, `config/session.php`.
- Bukti: contoh konfigurasi memakai `APP_DEBUG=true`, `SESSION_ENCRYPT=false`, umur session 120 menit, tidak mendeklarasikan secure cookie, expire-on-close, atau SameSite Strict.
- Dampak: operator yang menyalin konfigurasi tanpa hardening dapat mengekspos detail error dan melemahkan proteksi cookie/session.
- Perbaikan: sediakan template produksi tanpa secret dengan debug mati, cookie HTTPS, session terenkripsi, SameSite Strict, expire-on-close, dan lifetime lebih pendek.
- Hasil: `.env.production.example` aman dan tanpa secret; default session diperketat menjadi terenkripsi, 60 menit, expire-on-close, dan SameSite Strict.

### SEC-06 — Medium — Iframe dan URL eksternal masih dapat diperketat

- Status: **Selesai**
- Lokasi: `resources/views/public-site/home.blade.php:408`, `app/Http/Requests/Admin/SiteSettingRequest.php:57-61`.
- Bukti: URL Google Maps sudah di-allowlist, tetapi iframe belum memakai sandbox; URL media sosial hanya memakai validasi URL generik.
- Dampak: memperluas kemampuan konten iframe dan memungkinkan admin tersimpan mengarah ke skema/host eksternal yang tidak sesuai fungsi field.
- Perbaikan: sandbox iframe, referrer lebih ketat, hanya HTTPS, dan allowlist host per platform.
- Hasil: iframe Google Maps disandbox; seluruh field media sosial dibatasi ke HTTPS dan host resmi masing-masing.

## Kontrol yang Sudah Baik

- Semua route mutasi web dilindungi CSRF Laravel.
- Seluruh panel admin berada di balik middleware `auth`.
- Login dibatasi lima kegagalan berdasarkan email dan IP.
- Session ID diregenerasi setelah login; logout menginvalidasi session dan token CSRF.
- Rich-text artikel disanitasi menggunakan allowlist sebelum disimpan.
- Output Blade biasa di-escape; output HTML mentah hanya digunakan untuk hasil sanitizer atau teks yang sudah di-escape.
- Upload gambar dibatasi sebagai image, MIME, ukuran, dimensi, dan disimpan dengan nama acak.
- URL embed Google Maps sudah dibatasi ke HTTPS dan host/path resmi.
- `npm audit` tidak menemukan vulnerability.

## Kriteria Penutupan

Temuan dinyatakan selesai hanya jika patch diterapkan, test keamanan baru lulus, suite aplikasi penuh lulus, build produksi berhasil, audit Composer/npm bersih, dan header runtime terverifikasi.

## Hasil Validasi Akhir

- PHPUnit: **104 test, 613 assertion, seluruhnya lulus**.
- Test keamanan khusus: fingerprint mismatch, absolute timeout, single-login, pencabutan session, password lemah, CSP nonce, header, no-store, konfigurasi produksi, dan blokir PHP upload.
- Composer: konfigurasi valid dan **0 advisory**.
- npm: **0 vulnerability** untuk production maupun keseluruhan dependency.
- Build Vite produksi: berhasil.
- Pint dan `git diff --check`: lulus.
- Browser nyata: halaman publik, aset storage, musik, dan Alpine berjalan dengan **0 error dan 0 warning**.
- Runtime: CSP, `DENY`, `nosniff`, referrer policy, permissions policy, admin `no-store`, cookie `HttpOnly`, dan `SameSite=Strict` terverifikasi.
- Integritas data setelah suite: 1 admin, 1 konfigurasi situs, 3 artikel, 2 agenda, dan 20 pengurus tetap utuh.

## Risiko Residual

- Alpine standar masih memerlukan `'unsafe-eval'` untuk evaluasi ekspresi. CSP tetap memblokir script asing dan inline script tanpa nonce; rich-text sanitizer juga membuang atribut/event berbahaya. Migrasi ke Alpine CSP build dapat dipertimbangkan sebagai hardening lanjutan, tetapi bukan perubahan aman untuk dilakukan tanpa refactor dan QA seluruh ekspresi Alpine.
- Flag cookie `Secure` hanya boleh aktif pada HTTPS. Ia sengaja tidak aktif pada server HTTP lokal, tetapi diwajibkan dalam `.env.production.example`.
- Proteksi ini mengurangi risiko pembajakan secara signifikan, tetapi tidak menggantikan TLS hosting, patch server, backup, monitoring log, atau MFA.
