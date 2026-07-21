# Checklist Perbaikan FPK Kota Malang

Sumber kebutuhan: `TASK.md`.

## 1. Masa Bakti Pengurus

- [x] Audit seluruh sumber data dan hardcode masa bakti pada website publik serta panel admin.
- [x] Sediakan pengelolaan periode dengan tahun mulai, tahun selesai, dan status aktif/tidak aktif.
- [x] Terapkan validasi empat digit, tahun selesai lebih besar dari tahun mulai, dan tahun selesai lebih besar dari tahun berjalan.
- [x] Pastikan hanya satu periode yang dapat berstatus aktif dengan proteksi aplikasi dan database yang sesuai.
- [x] Gunakan periode aktif sebagai satu-satunya sumber masa bakti pada hero, Susunan Pengurus, foto bersama, daftar pengurus, dan bagian publik lainnya.
- [x] Tambahkan pesan validasi yang jelas dan pengujian untuk aturan periode.

## 2. Navigasi Artikel

- [x] Pastikan section Artikel Terbaru memiliki anchor `artikel`.
- [x] Arahkan menu Artikel pada navbar dan footer ke halaman utama `#artikel`, termasuk ketika dibuka dari halaman lain.
- [x] Pastikan tautan detail artikel hanya berada pada kartu, gambar, judul, atau tombol baca artikel.
- [x] Tambahkan pengujian navigasi Artikel.

## 3. Hero, Logo, dan Teks

- [x] Pisahkan gambar hero dari logo, nama organisasi, dan tagline dalam elemen HTML tersendiri.
- [x] Ambil nama organisasi dan tagline dari Pengaturan Website tanpa hardcode.
- [x] Tambahkan overlay/gradient yang menjaga keterbacaan pada gambar terang maupun gelap.
- [x] Pastikan fallback gambar hero dan logo tetap tersedia.
- [x] Validasi tampilan hero pada desktop dan mobile.

## 4. Pengaturan Website Terpadu

- [x] Audit dan hilangkan duplikasi field/sumber data antara Profil FPK, Pengaturan Website, serta Kontak.
- [x] Gunakan satu menu sidebar bernama Pengaturan Website dan hapus menu Profil FPK terpisah.
- [x] Satukan pengelolaan Identitas dan Branding, Beranda dan Hero, Tentang FPK, Kontak dan Media Sosial, serta SEO dalam satu halaman terstruktur.
- [x] Pertahankan seluruh data lama saat struktur pengaturan digabungkan.
- [x] Pastikan admin dapat berpindah bagian dengan navigasi yang jelas tanpa satu formulir panjang yang membingungkan.
- [x] Tambahkan pengujian render dan pembaruan untuk pengaturan terpadu.

## 5. Validasi Panjang Teks

- [x] Terapkan validasi backend: nama situs 60, nama organisasi 100, singkatan 20, tagline 120, judul hero 100, subtitle hero 180, dan teks footer 180 karakter.
- [x] Tambahkan pesan validasi Bahasa Indonesia yang menyebutkan batas karakter.
- [x] Tambahkan atribut `maxlength` pada field admin yang sesuai.
- [x] Tambahkan perlindungan wrapping teks pada layout publik.
- [x] Tambahkan pengujian yang memastikan data terlalu panjang ditolak dan tidak tersimpan.

## 6. Validasi Akhir

- [x] Jalankan migrasi pada database pengujian dan pastikan skema valid.
- [x] Jalankan seluruh test suite tanpa kegagalan.
- [x] Jalankan build front-end tanpa error atau warning relevan.
- [x] Audit ulang hardcode tahun, duplikasi field/menu, route, dan tautan Artikel.
- [x] Validasi browser untuk website publik dan panel admin pada desktop serta mobile.
- [x] Pastikan `git diff --check` bersih dan tidak ada artefak QA yang tertinggal.
