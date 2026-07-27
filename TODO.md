# Audit Checklist Progress TASK.md

Tanggal audit implementasi: 23 Juli 2026

## Ringkasan

- [x] Temuan 1 — Siklus hidup artikel dan agenda distandarkan sebagai arsip.
- [x] Temuan 2 — `map_embed_url` dibatasi ke URL embed resmi Google Maps.
- [x] Temuan 3 — Status agenda dibuat semi-otomatis berdasarkan waktu acara.
- [x] Tes regresi, format kode, build produksi, dan browser QA selesai.

## 1. Arsip Artikel dan Agenda

### Perilaku dan antarmuka

- [x] Mempertahankan `SoftDeletes` sebagai mekanisme arsip.
- [x] Mengganti label aksi utama dari **Hapus** menjadi **Arsipkan**.
- [x] Mengganti pesan sukses menjadi **berhasil diarsipkan**.
- [x] Menjelaskan pada dialog konfirmasi bahwa data masih dapat dipulihkan.
- [x] Menambahkan halaman **Arsip Artikel**.
- [x] Menambahkan halaman **Arsip Agenda**.
- [x] Menampilkan waktu data diarsipkan (`deleted_at`).
- [x] Menambahkan pencarian pada kedua halaman arsip.
- [x] Menambahkan aksi **Pulihkan**.
- [x] Menambahkan aksi **Hapus Permanen** dengan konfirmasi tegas.
- [x] Melindungi seluruh route arsip dengan autentikasi admin.

### Data dan file

- [x] File thumbnail/poster tetap disimpan ketika data hanya diarsipkan.
- [x] File thumbnail artikel dihapus setelah artikel dihapus permanen.
- [x] File poster agenda dihapus setelah agenda dihapus permanen.
- [x] Slug data yang masih berada di arsip tetap dicadangkan agar pemulihan tidak menimbulkan konflik URL.
- [x] Tidak memerlukan migrasi baru karena kolom `deleted_at` dan `SoftDeletes` sudah tersedia.

### Cakupan tes

- [x] Arsip artikel.
- [x] Daftar arsip artikel.
- [x] Pemulihan artikel beserta file thumbnail.
- [x] Penghapusan permanen artikel beserta file thumbnail.
- [x] Penolakan slug yang masih digunakan artikel dalam arsip.
- [x] Arsip agenda.
- [x] Daftar arsip agenda.
- [x] Pemulihan agenda beserta file poster.
- [x] Penghapusan permanen agenda beserta file poster.

## 2. Validasi URL Embed Google Maps

### Aturan backend

- [x] Input tetap berupa URL, bukan HTML `<iframe>`.
- [x] Scheme wajib `https`.
- [x] Host menggunakan allowlist eksak:
  - `google.com`
  - `www.google.com`
  - `maps.google.com`
- [x] Pola utama wajib menggunakan path `/maps/embed` dan memiliki query embed.
- [x] Format lama resmi `https://maps.google.com/maps?...&output=embed` tetap didukung.
- [x] URL dengan kredensial atau port selain `443` ditolak.
- [x] Domain mirip seperti `google.com.evil.example` ditolak.
- [x] URL website pihak ketiga, halaman Google Maps biasa, HTTP, dan raw iframe ditolak.
- [x] Pesan validasi menjelaskan format URL Google Maps yang benar.

### Antarmuka dan tes

- [x] Hint form admin menjelaskan agar admin menempel URL embed saja.
- [x] URL resmi Google Maps dapat disimpan.
- [x] Nilai lama tidak berubah ketika input berbahaya/tidak sesuai ditolak.
- [x] Penolakan domain non-Google diverifikasi langsung melalui browser.

## 3. Status Agenda Semi-Otomatis

### Aturan status

- [x] `now < starts_at` menghasilkan **Terjadwal**.
- [x] `starts_at <= now <= ends_at` menghasilkan **Berlangsung**.
- [x] `now > ends_at` menghasilkan **Selesai**.
- [x] Pilihan manual **Dibatalkan** selalu dipertahankan.
- [x] Jika `ends_at` kosong dan acara sudah dimulai, status menjadi **Berlangsung**.
- [x] Jika `ends_at` kosong, admin tetap dapat menandai agenda **Selesai** secara manual.
- [x] Status dinormalisasi ketika agenda dibuat atau diperbarui.
- [x] Status efektif dihitung ulang ketika ditampilkan sehingga tidak memerlukan scheduler.
- [x] Agenda selesai atau dibatalkan otomatis dikeluarkan dari seluruh halaman publik.
- [x] Detail agenda publik mengembalikan 404 setelah agenda selesai atau dibatalkan.
- [x] Agenda selesai/dibatalkan tidak lagi masuk sitemap publik.

### Konsistensi tampilan

- [x] Daftar agenda admin memakai status efektif.
- [x] Form edit memilih status efektif terbaru.
- [x] Kartu agenda pada beranda memakai status efektif.
- [x] Detail agenda publik memakai status efektif.
- [x] Query beranda mengambil `ends_at` agar perhitungan status lengkap.
- [x] Agenda yang sedang berlangsung tetap masuk kelompok publik **Mendatang & Berlangsung**.
- [x] Bantuan pada form admin menjelaskan bagian otomatis dan kontrol manual.

### Validasi waktu dan tes

- [x] `starts_at` tetap wajib.
- [x] `ends_at` tetap opsional.
- [x] Jika diisi, `ends_at` wajib lebih besar daripada `starts_at`.
- [x] Pesan kesalahan waktu selesai dibuat spesifik dalam Bahasa Indonesia.
- [x] Status masa depan, sedang berlangsung, selesai, dibatalkan, dan tanpa waktu selesai tercakup tes.
- [x] Perubahan status tanpa scheduler tercakup tes.
- [x] Status efektif pada daftar admin dan detail publik tercakup tes.
- [x] Pengelompokan agenda berlangsung dan terdahulu tercakup tes.

## 4. Riwayat dan Log Agenda

- [x] Menambahkan tabel `agenda_logs` dengan relasi agenda dan admin.
- [x] Mencatat agenda dibuat, diperbarui, diarsipkan, dan dipulihkan.
- [x] Mencatat status sebelum/sesudah serta field yang berubah.
- [x] Menambahkan halaman Riwayat Agenda untuk agenda selesai, dibatalkan, dan terarsip.
- [x] Menambahkan halaman detail Log Agenda.
- [x] Riwayat tetap tersedia di admin meskipun agenda sudah tidak tampil di publik.
- [x] Menyediakan penghapusan permanen langsung dari riwayat.
- [x] Penghapusan permanen membersihkan agenda, poster, dan seluruh log terkait.
- [x] Restore hanya tersedia untuk agenda yang benar-benar diarsipkan.

## 5. Bukti Validasi

- [x] Route audit mencakup route riwayat, log, arsip, pulihkan, dan hapus permanen agenda.
- [x] Tes terarah awal: 18 tes, 129 assertion — lulus.
- [x] Tes render admin/publik: 26 tes, 135 assertion — lulus.
- [x] Tes alur agenda/publik terbaru: 36 tes, 205 assertion — lulus.
- [x] Full test suite terbaru: 77 tes, 382 assertion — lulus.
- [x] Laravel Pint pada seluruh file PHP yang berubah — lulus.
- [x] `git diff --check` — lulus.
- [x] Vite production build — lulus.
- [x] Browser QA desktop — halaman arsip, form agenda, dan pengaturan kontak berhasil dirender.
- [x] Browser QA mobile 390 px — tidak ada horizontal overflow pada halaman arsip artikel maupun agenda.
- [x] Browser QA terbaru — Riwayat Agenda dan Log Agenda berhasil dirender pada desktop/mobile.
- [x] Browser QA terbaru — agenda selesai terkonfirmasi tidak tampil pada beranda publik.
- [x] Browser console — 0 error.
- [x] Akun browser QA sementara dan artefak screenshot sementara sudah dibersihkan.

## 6. Status Akhir

- [x] Seluruh rekomendasi implementatif dalam `TASK.md` telah diselesaikan.
- [x] Tidak ada item implementasi `TASK.md` yang masih tertunda.
- [x] Perubahan lokal lain yang sudah ada sebelum pekerjaan ini dipertahankan dan tidak dihapus.
