## TASK ##

Melakukan perbaikan pada panel admin dan website publik FPK Kota Malang agar data yang ditampilkan konsisten, mudah diperbarui oleh admin, dan tidak memerlukan perubahan langsung pada source code setiap kali informasi organisasi berubah.

Perbaikan difokuskan pada:

Pengelolaan masa bakti pengurus.
Navigasi menu Artikel.
Pemisahan gambar hero dari logo dan teks.
Penggabungan pengaturan konten website.
Validasi panjang teks pada panel admin.
1. Membuat Masa Bakti Pengurus Dapat Dikelola dari Panel Admin
Permasalahan
Informasi masa bakti saat ini belum konsisten pada seluruh bagian website.

Sebagai contoh, pada salah satu bagian dapat tampil masa bakti 2025–2027, sedangkan pada bagian Susunan Pengurus atau keterangan foto bersama masih menampilkan periode lama seperti 2014–2021.

Hal ini kemungkinan terjadi karena informasi masa bakti masih berasal dari beberapa sumber data atau sebagian masih ditulis langsung di dalam file Blade.

Perbaikan yang Dibutuhkan
Tambahkan pengelolaan masa bakti atau periode kepengurusan melalui panel admin dengan field minimal:

Tahun mulai.
Tahun selesai.
Status periode, misalnya Aktif atau Tidak Aktif.
Contoh data:

Tahun mulai: 2025
Tahun selesai: 2027
Status: Aktif
Ketentuan Validasi
Terapkan validasi berikut:

Tahun mulai wajib berupa angka empat digit.
Tahun selesai wajib berupa angka empat digit.
Tahun selesai harus lebih besar dari tahun mulai.
Tahun selesai harus lebih besar dari tahun saat ini.
Hanya boleh ada satu periode yang berstatus aktif.
Sistem harus menampilkan pesan kesalahan yang jelas apabila data tidak valid.
noti]f nyal ini loh ya:

Tahun selesai harus lebih besar dari tahun mulai dan lebih besar dari tahun saat ini.

Penggunaan Data
Periode yang berstatus aktif harus digunakan sebagai satu-satunya sumber data masa bakti pada seluruh website publik, termasuk:

Informasi masa bakti pada bagian hero.
Subtitle pada bagian Susunan Pengurus.
Keterangan foto bersama pengurus.
Daftar pengurus.
Bagian lain yang menampilkan tahun kepengurusan.
Jangan menulis tahun masa bakti secara langsung di dalam file Blade.
2. Memperbaiki Navigasi Menu Artikel
Permasalahan
Menu pada navbar seperti Tentang FPK, Agenda, dan Pengurus akan melakukan scroll menuju section yang sesuai pada halaman utama.

Namun, menu Artikel memiliki perilaku yang berbeda. Ketika diklik, pengguna justru diarahkan langsung ke halaman atau detail salah satu artikel.

Perilaku yang Diharapkan
Menu Artikel pada navbar dan footer harus mengarahkan pengguna ke section Artikel Terbaru pada halaman utama.

Tautan menuju halaman detail artikel hanya digunakan pada:

Gambar artikel.
Judul artikel.
Kartu artikel.
Tombol Baca selengkapnya.
Tombol lain yang memang ditujukan untuk membuka detail artikel.
Saran Implementasi
Berikan ID pada section artikel, misalnya:

<section id="artikel">
    <!-- Konten artikel -->
</section>
Tautan menu Artikel pada halaman utama dapat menggunakan:

<a href="#artikel">Artikel</a>
Apabila menu Artikel diakses dari halaman lain, arahkan pengguna kembali ke halaman utama beserta anchor section artikel.
3. Memisahkan Gambar Hero dari Logo dan Teks
Permasalahan
Pada gambar hero bawaan terdapat logo FPK, nama organisasi, dan tagline.

Namun, ketika gambar hero diganti melalui panel admin, logo dan teks tersebut ikut menghilang.

Hal ini menunjukkan bahwa logo dan tulisan kemungkinan masih menyatu di dalam file gambar lama, bukan dibuat sebagai elemen HTML yang terpisah.

Perbaikan yang Dibutuhkan
Gambar hero hanya digunakan sebagai gambar utama atau background.

Elemen berikut harus dibuat terpisah dari file gambar:

Logo FPK.
Nama organisasi.
Tagline atau subtitle.
Overlay atau gradient untuk menjaga keterbacaan tulisan.
Contoh struktur komponen:

<div class="relative overflow-hidden rounded-2xl">
    <img
        src="{{ $heroImage }}"
        alt="Gambar FPK Kota Malang"
        class="h-full w-full object-cover"
    >

    <div class="absolute inset-0 bg-black/40"></div>

    <div class="absolute inset-0 flex flex-col items-center justify-center text-center text-white">
        <img
            src="{{ $logo }}"
            alt="Logo FPK Kota Malang"
            class="mb-4 h-24 w-24 object-contain"
        >

        <h2 class="text-2xl font-semibold">
            {{ $organizationName }}
        </h2>

        <p class="mt-2">
            {{ $tagline }}
        </p>
    </div>
</div>
Nama organisasi dan tagline harus mengambil data dari Pengaturan Website, bukan ditulis langsung di dalam komponen.

Ketentuan Tampilan
Gambar menggunakan object-cover agar memenuhi area kartu.
Logo dan teks tetap tampil ketika gambar hero diganti.
Tambahkan overlay agar tulisan tetap terbaca pada gambar terang maupun gelap.
Posisi logo dan teks harus tetap rapi pada desktop dan mobile.
Gunakan gambar default apabila gambar hero belum tersedia.
Gunakan logo default apabila logo organisasi belum tersedia.
4. Menggabungkan Profil FPK dan Pengaturan Website
Permasalahan
Saat ini terdapat menu Profil FPK dan Pengaturan Website yang sama-sama digunakan untuk mengubah konten pada website publik.

Pemisahan tersebut dapat membingungkan admin karena admin harus mencari halaman yang digunakan untuk mengubah bagian tertentu.

Selain itu, terdapat risiko data yang sama tersedia pada dua halaman berbeda, misalnya:

Nama organisasi.
Tagline.
Logo.
Judul hero.
Informasi profil organisasi.
Perbaikan yang Dibutuhkan
Gunakan satu menu utama pada sidebar dengan nama:

Pengaturan Website

Menu Profil FPK tidak perlu ditampilkan sebagai tombol atau menu sidebar yang terpisah.

Ketika menu Pengaturan Website dibuka, admin dapat memilih bagian website yang ingin diedit melalui:

Tab.
Submenu.
Accordion.
Kartu pilihan.
Navigasi section di dalam halaman.
Jangan membuat seluruh pengaturan menjadi satu formulir panjang tanpa pembagian yang jelas.

Struktur yang Disarankan
A. Identitas dan Branding
Berisi:

Nama situs.
Nama organisasi.
Singkatan organisasi.
Tagline.
Logo.
Favicon.
Gambar Open Graph.
Teks footer.
B. Beranda dan Hero
Berisi:

Judul hero.
Subtitle hero.
Gambar hero.
Informasi singkat organisasi.
Informasi lembaga.
Masa bakti aktif.
C. Tentang FPK
Berisi data yang saat ini sudah tersedia:

Pengertian.
Latar belakang.
Tujuan.
Tugas pokok.
Dasar hukum.
Gambar atau ilustrasi Tentang FPK.
Bagian Tentang FPK tidak perlu didesain ulang secara besar-besaran. Cukup dipindahkan dan dikelompokkan ke dalam menu Pengaturan Website.

D. Kontak dan Media Sosial
Berisi:

Alamat.
Nomor telepon.
Email.
Instagram.
Facebook.
YouTube.
Tautan Google Maps atau lokasi kantor.
E. SEO
Berisi:

Meta title default.
Meta description default.
Keyword.
Gambar Open Graph default.
Ketentuan Penting
Data yang sama tidak boleh tersedia pada dua bagian pengaturan.
Nama organisasi hanya memiliki satu sumber data.
Tagline hanya memiliki satu sumber data.
Logo hanya memiliki satu sumber data.
Judul hero hanya memiliki satu sumber data.
Data lama yang sudah tersimpan harus tetap dipertahankan.
Menu Profil FPK tidak lagi tampil secara terpisah.
Seluruh pengaturan tetap berada di dalam satu menu Pengaturan Website.
Admin dapat memilih bagian yang ingin diedit tanpa harus membuka menu berbeda-beda.
5. Menambahkan Validasi Panjang Teks
Permasalahan
Beberapa field pada panel admin dapat menerima teks yang terlalu panjang tanpa pembatasan yang jelas.

Hal tersebut dapat menyebabkan:

Nama situs merusak susunan navbar.
Judul hero terlalu banyak baris.
Tagline keluar dari area yang tersedia.
Footer menjadi tidak rapi.
Tampilan mobile terpotong.
Ukuran kartu berubah secara tidak terkendali.
Perbaikan yang Dibutuhkan
Tambahkan validasi panjang karakter pada sisi backend.

Validasi pada frontend juga dapat ditambahkan agar admin mengetahui batas karakter sebelum formulir dikirim.

Batas awal yang disarankan:

Field	Maksimal karakter
Nama situs	60
Nama organisasi	100
Singkatan	20
Tagline	120
Judul hero	100
Subtitle hero	180
Teks footer	180
Batas tersebut dapat disesuaikan apabila kebutuhan desain berbeda.

Contoh Validasi Laravel
$request->validate([
    'site_name' => ['required', 'string', 'max:60'],
    'organization_name' => ['required', 'string', 'max:100'],
    'abbreviation' => ['nullable', 'string', 'max:20'],
    'tagline' => ['nullable', 'string', 'max:120'],
    'hero_title' => ['required', 'string', 'max:100'],
    'hero_subtitle' => ['nullable', 'string', 'max:180'],
    'footer_text' => ['nullable', 'string', 'max:180'],
]);
Contoh pembatasan pada form:

<input
    type="text"
    name="hero_title"
    maxlength="100"
    value="{{ old('hero_title', $settings->hero_title) }}"
>
Ketentuan Implementasi
Validasi utama wajib dilakukan pada backend.
Data tidak boleh tersimpan apabila melebihi batas.
Tampilkan pesan kesalahan yang menjelaskan batas karakter.
Tambahkan atribut maxlength pada input yang sesuai.
Gunakan CSS seperti overflow-wrap untuk mencegah teks merusak layout.
Character counter tidak wajib apabila ingin mempertahankan tampilan yang sederhana.
Contoh pesan validasi:

Judul hero tidak boleh lebih dari 100 karakter.