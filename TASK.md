Hasil Recheck Tiga Temuan Utama
Saya sudah melakukan pengecekan ulang terhadap tiga poin berikut.

Pada proses recheck ini, tidak ada perubahan kode yang saya lakukan kayaknya wkwkw. Pengecekan hanya dilakukan untuk memastikan kondisi implementasi saat ini, dampaknya terhadap admin dan website publik, serta perbaikan yang paling masuk akal.

Setelah diperiksa ulang, tiga poin ini memang menjadi temuan yang paling layak dicatat karena berkaitan langsung dengan:

Kejelasan perilaku fitur.
Keamanan input dari panel admin.
Konsistensi informasi pada website publik.
Kemudahan pengelolaan konten oleh admin.
1. Fitur Hapus Artikel dan Agenda Sebenarnya Berperilaku sebagai Arsip
Referensi File
app/Http/Controllers/Admin/ArticleController.php sekitar baris 88.
app/Http/Controllers/Admin/AgendaController.php sekitar baris 91.
app/Models/Article.php sekitar baris 28.
app/Models/Agenda.php sekitar baris 29.
app/Http/Requests/Admin/ArticleRequest.php sekitar baris 40.
app/Http/Requests/Admin/AgendaRequest.php sekitar baris 41.
Kondisi Saat Ini
Pada panel admin, tersedia tombol untuk menghapus artikel dan agenda.

Ketika tombol tersebut dijalankan, controller memanggil:

$article->delete()
$agenda->delete()
Namun, model Article dan Agenda menggunakan trait Laravel:

use SoftDeletes;
Dengan penggunaan SoftDeletes, pemanggilan method delete() tidak benar-benar menghapus data dari database.

Data hanya akan diperbarui dengan mengisi kolom:

deleted_at
Setelah kolom deleted_at terisi, query Eloquent biasa secara otomatis tidak lagi menampilkan data tersebut.

Akibatnya, dari sisi admin data terlihat sudah hilang, padahal sebenarnya data masih tersimpan di dalam database.

Pesan yang tampil setelah proses tersebut juga masih menggunakan istilah seperti:

Artikel berhasil dihapus.

atau:

Agenda berhasil dihapus.

Secara teknis, proses tersebut bukan penghapusan permanen, melainkan pengarsipan menggunakan mekanisme soft delete.

Permasalahan Utama
Masalah utamanya bukan pada penggunaan SoftDeletes, karena soft delete justru dapat menjadi pilihan yang baik.

Masalahnya adalah terdapat ketidaksesuaian antara:

Istilah yang digunakan pada tombol.
Pesan sukses yang ditampilkan.
Ekspektasi admin.
Perilaku sebenarnya di backend.
Saat admin melihat tombol Hapus, admin kemungkinan akan memahami bahwa data benar-benar dihapus secara permanen.

Padahal data tersebut hanya disembunyikan dari query normal.

Dengan kata lain, fitur saat ini secara perilaku adalah arsip, tetapi secara tampilan masih disebut hapus.

Fitur Pendukung yang Belum Tersedia
Saat ini sistem sudah menggunakan soft delete, tetapi belum memiliki fitur lanjutan yang biasanya menyertai sistem arsip, seperti:

Halaman daftar artikel yang diarsipkan.
Halaman daftar agenda yang diarsipkan.
Tombol untuk mengembalikan data atau restore.
Tombol untuk menghapus permanen atau force delete.
Informasi kapan data diarsipkan.
Konfirmasi yang menjelaskan bahwa data masih dapat dikembalikan.
Karena fitur-fitur tersebut belum tersedia, data yang sudah di-soft-delete menjadi tidak terlihat dan tidak dapat dikelola melalui panel admin.

Datanya masih ada di database, tetapi admin tidak mempunyai jalur untuk melihat atau mengembalikannya.

Masalah pada Slug
Artikel dan agenda menggunakan slug sebagai bagian dari URL.

Contoh:

/artikel/kegiatan-fpk-kota-malang
atau:

/agenda/rapat-koordinasi-fpk
Ketika artikel atau agenda di-soft-delete, data tersebut masih berada di database.

Jika validasi slug menggunakan aturan unique biasa, slug dari data yang sudah diarsipkan tetap dianggap masih digunakan.

Akibatnya, admin dapat mengalami kondisi berikut:

Admin membuat artikel dengan slug kegiatan-fpk.
Artikel tersebut kemudian dihapus melalui panel admin.
Artikel sebenarnya hanya di-soft-delete.
Admin ingin membuat artikel baru dengan slug yang sama.
Sistem menolak karena slug dianggap sudah digunakan.
Dari sudut pandang admin, kondisi ini membingungkan karena artikel lama sudah tidak terlihat, tetapi slug-nya masih tidak dapat digunakan kembali.

Jika konsep yang dipilih adalah arsip, hal tersebut sebenarnya masih masuk akal karena data lama tetap disimpan.

Namun, sistem perlu memberikan jalur untuk melihat dan mengelola data yang sudah diarsipkan.

Kondisi File Gambar
Artikel dan agenda dapat memiliki file gambar seperti:

Thumbnail artikel.
Poster agenda.
Gambar pendukung lainnya.
Ketika data hanya di-soft-delete, file gambar tersebut juga tetap tersimpan di storage.

Kondisi ini bukan kesalahan apabila fitur memang dirancang sebagai arsip.

File harus tetap tersedia karena data dapat dipulihkan kembali.

Namun, jika admin menganggap tombol tersebut sebagai hapus permanen, admin dapat mengira bahwa:

Data sudah hilang.
File sudah dihapus.
Ruang penyimpanan sudah dibersihkan.
Padahal data dan file masih tetap ada.

Oleh karena itu, definisi fitur harus diperjelas terlebih dahulu.

Dampak Praktis
Bagi Admin
Admin dapat salah memahami fungsi tombol hapus.
Admin mengira data sudah benar-benar hilang.
Admin tidak dapat melihat data yang telah diarsipkan.
Admin tidak dapat melakukan restore.
Admin tidak dapat menggunakan kembali slug lama.
Admin tidak mengetahui bahwa file gambar masih tersimpan.
Bagi Sistem
Data yang sudah diarsipkan terus bertambah di database.
File terkait tetap tersimpan di storage.
Tidak tersedia pengelolaan siklus hidup data.
Istilah pada UI tidak sesuai dengan proses backend.
Bagi Pengembangan Selanjutnya
Apabila suatu saat dibutuhkan audit, pemulihan data, atau pelacakan konten lama, data sebenarnya masih tersedia.

Namun, karena belum ada UI arsip, pemulihan hanya dapat dilakukan melalui database atau kode.

Pilihan Perbaikan
Sebelum implementasi, perlu ditentukan definisi bisnis fitur ini.

Terdapat dua pilihan.

Opsi A — Tetap Menggunakan Sistem Arsip
Pada opsi ini, penggunaan SoftDeletes tetap dipertahankan.

Aksi utama tidak lagi disebut sebagai Hapus, tetapi sebagai:

Arsipkan

Pesan sukses juga disesuaikan menjadi:

Artikel berhasil diarsipkan.

atau:

Agenda berhasil diarsipkan.

Kemudian ditambahkan fitur:

Daftar artikel yang diarsipkan.
Daftar agenda yang diarsipkan.
Restore data.
Hapus permanen sebagai aksi tambahan.
Konfirmasi sebelum hapus permanen.
Opsi B — Menggunakan Hapus Permanen
Pada opsi ini, tombol Hapus benar-benar menghapus data secara permanen.

Proses backend dapat menggunakan:

forceDelete()
Selain menghapus data dari database, sistem juga perlu:

Menghapus thumbnail artikel.
Menghapus poster agenda.
Menghapus file terkait yang tidak lagi digunakan.
Memberikan konfirmasi yang lebih tegas.
Menjelaskan bahwa data tidak dapat dikembalikan.
Opsi ini lebih sederhana dari sisi UI, tetapi memiliki risiko kehilangan data akibat kesalahan admin.

Rekomendasi
Untuk website institusi, organisasi, dan pemerintahan, saya lebih menyarankan agar fitur ini distandarkan sebagai arsip.

Alasannya:

Lebih aman apabila admin salah menekan tombol.
Artikel dan agenda lama masih dapat dibutuhkan untuk dokumentasi.
Data lama dapat digunakan untuk audit atau riwayat kegiatan.
Mengurangi risiko kehilangan konten secara permanen.
Sesuai untuk konten berita, dokumentasi, dan agenda organisasi.
Perbaikan minimal yang paling penting adalah:

Ubah istilah Hapus menjadi Arsipkan.
Ubah pesan sukses agar sesuai.
Jelaskan bahwa data masih dapat dikembalikan.
Perbaikan ideal adalah:

Tambahkan halaman arsip.
Tambahkan fitur restore.
Tambahkan hapus permanen sebagai aksi terpisah.
Hapus file hanya ketika permanent delete dilakukan.
2. Field map_embed_url Belum Dibatasi Khusus untuk Google Maps
Referensi File
app/Http/Requests/Admin/SiteSettingRequest.php sekitar baris 47.
resources/views/admin/settings/edit.blade.php sekitar baris 129.
resources/views/public-site/home.blade.php sekitar baris 408.
Kondisi Saat Ini
Pada panel admin, tersedia field dengan label:

URL Embed Google Maps

Secara tampilan, field tersebut menunjukkan bahwa admin seharusnya hanya memasukkan URL embed dari Google Maps.

Namun, validasi backend saat ini masih menggunakan aturan URL biasa.

Secara konsep, validasinya kurang lebih hanya memeriksa bahwa input merupakan URL yang valid.

Setelah disimpan, URL tersebut langsung digunakan pada atribut:

<iframe src="...">
Artinya, selama input dianggap sebagai URL valid, sistem dapat menerima URL yang bukan berasal dari Google Maps.

Permasalahan Utama
Masalahnya adalah terdapat ketidaksesuaian antara tujuan field dan aturan backend.

Pada UI, field disebut sebagai:

URL Embed Google Maps

Namun, backend hanya memastikan bahwa input merupakan URL, tanpa memastikan bahwa URL tersebut benar-benar berasal dari Google Maps.

Akibatnya, admin secara teknis dapat memasukkan:

URL website lain.
URL halaman pihak ketiga.
URL embed non-Google Maps.
Sumber iframe yang tidak diinginkan.
Ini bukan berarti pengunjung publik dapat mengubah URL tersebut secara langsung.

Risiko utamanya berasal dari:

Admin salah menyalin URL.
Admin memasukkan URL yang tidak sesuai.
Akun admin digunakan oleh orang lain.
Terjadi akses tidak sah ke panel admin.
URL lama diganti dengan sumber pihak ketiga.
Jalur Risiko
Alur risikonya dapat terjadi seperti berikut:

Pengguna memiliki akses ke panel admin.
Pengguna membuka Pengaturan Website.
Pengguna mengisi field URL Embed Google Maps.
URL yang dimasukkan bukan URL Google Maps.
Backend tetap menerima karena URL tersebut valid.
Frontend memasukkan URL itu ke dalam iframe.
Website publik menampilkan konten pihak ketiga.
Karena iframe ditampilkan pada bagian kontak resmi, pengunjung dapat menganggap konten tersebut sebagai bagian resmi dari website FPK.

Dampak Praktis
Dampak terhadap Website Publik
Section kontak dapat menampilkan konten selain peta.
Tampilan kontak resmi menjadi tidak sesuai.
Pengunjung dapat diarahkan melihat konten pihak ketiga.
Informasi lokasi organisasi dapat menjadi salah.
Dampak terhadap Keamanan
Website menerima sumber iframe yang terlalu bebas.
Konten pihak ketiga dapat ditampilkan tanpa pembatasan domain.
Risiko meningkat apabila akun admin disalahgunakan.
Dampak terhadap Reputasi
Bagian kontak biasanya dianggap sebagai informasi resmi.

Jika iframe menampilkan konten yang salah, tidak relevan, atau mencurigakan, hal tersebut dapat mengurangi kepercayaan pengunjung terhadap website.

Perbaikan yang Dibutuhkan
Validasi sebaiknya tidak hanya memastikan bahwa input merupakan URL.

Sistem juga perlu memastikan bahwa:

URL menggunakan protokol yang aman.
Host termasuk dalam daftar domain yang diizinkan.
URL memiliki pola embed Google Maps yang benar.
Input hanya berupa URL, bukan kode iframe mentah.
Domain yang Dapat Diizinkan
Domain yang diperbolehkan dapat dibatasi, misalnya:

www.google.com
google.com
maps.google.com
Apabila implementasi embed menggunakan domain Google lain, domain tersebut dapat ditambahkan setelah diperiksa kebutuhannya.

Sebaiknya gunakan pendekatan allowlist.

Artinya, sistem hanya menerima domain yang secara eksplisit diizinkan.

Jangan menggunakan pemeriksaan longgar seperti:

URL mengandung kata "google"
Pemeriksaan seperti itu tidak aman karena domain lain dapat saja mengandung kata google pada subdomain, path, atau query string.

Pola URL yang Perlu Diperiksa
Selain domain, sistem juga sebaiknya memeriksa bahwa URL memang merupakan URL embed Google Maps.

Contoh pola yang umum digunakan:

https://www.google.com/maps/embed?pb=...
Sistem dapat memeriksa beberapa bagian:

Scheme harus https.
Host harus berada dalam allowlist.
Path harus sesuai dengan endpoint embed Google Maps.
URL tidak boleh menggunakan domain lain.
URL tidak boleh berisi skema seperti javascript: atau data:.
Input yang Harus Disimpan
Sistem sebaiknya tetap menyimpan:

URL embed saja

Contoh:

https://www.google.com/maps/embed?pb=...
Jangan meminta admin memasukkan raw HTML seperti:

<iframe src="..." width="600" height="450"></iframe>
Menerima raw iframe akan membuat validasi menjadi lebih sulit dan membuka ruang bagi atribut HTML lain yang tidak diperlukan.

Frontend cukup membuat elemen iframe sendiri menggunakan URL yang sudah tervalidasi.

Rekomendasi
Perbaikan paling aman adalah menerapkan:

Allowlist host dan validasi pola URL embed Google Maps

Validasi jangan hanya menggunakan aturan url.

Urutan validasi yang disarankan:

Pastikan input merupakan URL.
Pastikan menggunakan HTTPS.
Ambil host dari URL.
Cocokkan host dengan daftar domain yang diperbolehkan.
Periksa path URL embed.
Tolak URL apabila tidak sesuai.
Pesan Validasi
Apabila URL tidak sesuai, sistem harus menampilkan pesan yang mudah dipahami.

Contoh:

URL lokasi harus menggunakan URL embed resmi dari Google Maps.

atau:

Masukkan URL Google Maps dengan format https://www.google.com/maps/embed?....

Pesan jangan hanya berbunyi:

URL tidak valid.

Karena URL tersebut mungkin valid secara umum, tetapi tidak valid untuk kebutuhan Google Maps.
3. Status Agenda Perlu Dibuat Semi-Otomatis Berdasarkan Waktu Acara
Referensi File
app/Enums/AgendaStatus.php sekitar baris 7.
app/Http/Requests/Admin/AgendaRequest.php sekitar baris 50.
resources/views/admin/agendas/_form.blade.php sekitar baris 22.
app/Models/Agenda.php sekitar baris 34.
Kondisi Saat Ini
Status agenda sudah menggunakan enum dengan empat pilihan:

Terjadwal
Berlangsung
Selesai
Dibatalkan
Penggunaan enum merupakan hal yang baik karena admin tidak dapat memasukkan status sembarangan.

Namun, penentuan status saat ini masih sepenuhnya dilakukan secara manual.

Belum ada hubungan otomatis antara:

event_status
starts_at
ends_at
Waktu saat ini
Artinya, backend hanya memeriksa bahwa status termasuk dalam daftar enum, tetapi belum memeriksa apakah status tersebut sesuai dengan waktu agenda.

Permasalahan Utama
Karena status masih manual penuh, admin dapat memilih kombinasi waktu dan status yang tidak masuk akal.

Contoh kondisi yang masih mungkin diterima oleh sistem:

Contoh 1
Waktu acara: besok.
Status: Selesai.
Secara waktu, acara belum dimulai, tetapi sudah ditandai selesai.

Contoh 2
Waktu acara: bulan lalu.
Status: Terjadwal.
Secara waktu, acara sudah berlalu, tetapi masih dianggap akan datang.

Contoh 3
Waktu acara: minggu depan.
Status: Berlangsung.
Acara belum dimulai, tetapi ditampilkan sedang berlangsung.

Contoh 4
Waktu acara telah selesai.
Status tetap Berlangsung.
Admin lupa memperbarui status setelah acara berakhir.

Semua kombinasi tersebut valid dari sisi enum, tetapi tidak valid dari sisi logika waktu.

Dampak Praktis
Bagi Pengunjung
Pengunjung dapat melihat status yang tidak akurat.
Agenda lama dapat terlihat masih akan berlangsung.
Agenda yang belum dimulai dapat terlihat sudah selesai.
Informasi kegiatan organisasi menjadi membingungkan.
Bagi Admin
Admin harus selalu mengubah status secara manual.
Admin dapat lupa memperbarui status.
Admin harus memeriksa agenda satu per satu.
Risiko kesalahan input menjadi lebih tinggi.
Bagi Konsistensi Sistem
Tanggal dan badge status dapat saling bertentangan.
Website publik menampilkan informasi yang tidak konsisten.
Data agenda menjadi kurang dapat dipercaya.
Perbaikan yang Disarankan
Status agenda sebaiknya dibuat semi-otomatis.

Semi-otomatis berarti:

Sistem membantu menentukan status berdasarkan waktu.
Admin tetap memiliki kontrol untuk kondisi tertentu.
Status Dibatalkan tetap ditentukan secara manual.
Pendekatan ini lebih realistis dibandingkan status otomatis penuh.

Aturan Status yang Disarankan
1. Status Dibatalkan
Jika admin memilih:

Dibatalkan
Sistem harus mempertahankan status tersebut.

Status ini tidak boleh diubah otomatis berdasarkan waktu karena pembatalan merupakan keputusan organisasi atau admin.

Contoh:

Acara masih dua minggu lagi.
Admin membatalkan acara.
Status tetap Dibatalkan.
Sistem tidak mengubahnya menjadi Terjadwal.
2. Status Terjadwal
Jika acara belum dimulai:

now < starts_at
Maka status otomatis menjadi:

Terjadwal
Contoh:

starts_at: 25 Juli 2026 pukul 09.00.
Waktu sekarang: 22 Juli 2026.
Status: Terjadwal.
3. Status Berlangsung
Jika waktu saat ini berada di antara waktu mulai dan waktu selesai:

starts_at <= now <= ends_at
Maka status menjadi:

Berlangsung
Contoh:

Mulai: 22 Juli 2026 pukul 09.00.
Selesai: 22 Juli 2026 pukul 12.00.
Waktu sekarang: 22 Juli 2026 pukul 10.00.
Status: Berlangsung.
4. Status Selesai
Jika waktu saat ini sudah melewati waktu selesai:

now > ends_at
Maka status menjadi:

Selesai
Contoh:

Selesai: 22 Juli 2026 pukul 12.00.
Waktu sekarang: 22 Juli 2026 pukul 15.00.
Status: Selesai.
Aturan Utama
Secara ringkas:

Kondisi	Status
Admin memilih dibatalkan	Dibatalkan
Waktu sekarang sebelum starts_at	Terjadwal
Waktu sekarang antara starts_at dan ends_at	Berlangsung
Waktu sekarang melewati ends_at	Selesai
Penanganan ends_at Kosong
Hal yang perlu diperhatikan adalah kemungkinan field ends_at tidak diisi.

Jika ends_at kosong, sistem hanya mengetahui kapan acara dimulai, tetapi tidak mengetahui kapan acara selesai.

Contoh:

starts_at: 22 Juli 2026 pukul 09.00.
ends_at: kosong.
Setelah pukul 09.00, sistem dapat menentukan bahwa acara sudah dimulai.

Namun, sistem tidak mempunyai dasar untuk menentukan kapan status harus berubah menjadi Selesai.

Pilihan Penanganan ends_at Kosong
Opsi A — Setelah Mulai Tetap Berlangsung
Aturannya:

Sebelum starts_at: Terjadwal.
Setelah starts_at: Berlangsung.
Status berubah menjadi Selesai hanya setelah admin memperbaruinya secara manual.
Kelebihan:

Fleksibel.
Cocok untuk acara yang waktu selesainya tidak pasti.
Tidak memaksa admin mengisi waktu selesai.
Kekurangan:

Admin tetap harus mengubah status menjadi selesai.
Agenda dapat terus berstatus berlangsung jika admin lupa.
Opsi B — Wajib Mengisi Waktu Selesai
Aturannya:

ends_at wajib diisi.
Sistem dapat menentukan seluruh status secara otomatis.
Kelebihan:

Status dapat diperbarui dengan lebih konsisten.
Sistem mengetahui kapan acara selesai.
Kekurangan:

Tidak semua agenda memiliki waktu selesai yang pasti.
Dapat menyulitkan admin pada acara tertentu.
Rekomendasi untuk ends_at
Pendekatan paling realistis adalah:

ends_at tetap boleh kosong.
Jika waktu sekarang belum mencapai starts_at, status Terjadwal.
Jika waktu sekarang sudah melewati starts_at dan ends_at kosong, status Berlangsung.
Perubahan menjadi Selesai dilakukan manual oleh admin.
Jika admin mengisi ends_at, sistem dapat menentukan status selesai secara otomatis.
Dengan cara ini, sistem tetap membantu admin tanpa memaksakan data yang belum tentu tersedia.

Waktu Pembaruan Status
Perlu ditentukan kapan status dihitung ulang.

Terdapat beberapa pendekatan.

Opsi 1 — Dihitung Saat Data Ditampilkan
Status dihitung berdasarkan waktu setiap kali agenda ditampilkan.

Kelebihan:

Status selalu mengikuti waktu terbaru.
Tidak membutuhkan scheduler.
Kekurangan:

Nilai di database mungkin tetap menggunakan status lama.
Perlu memastikan seluruh tampilan memakai status hasil perhitungan.
Opsi 2 — Diperbarui Menggunakan Scheduler
Sistem menjalankan task secara berkala untuk memperbarui status agenda.

Contoh:

Setiap 5 menit atau setiap jam
Kelebihan:

Nilai status di database selalu diperbarui.
Mudah digunakan oleh query dan filter.
Kekurangan:

Membutuhkan konfigurasi scheduler pada server.
Lebih kompleks untuk website sederhana.
Rekomendasi
Untuk website yang ingin tetap sederhana, status dapat dihitung ketika agenda ditampilkan atau ketika data disimpan.

Namun, aturan harus digunakan secara konsisten pada:

Halaman admin.
Halaman publik.
Detail agenda.
Filter agenda.
Badge status.
Jangan sampai halaman admin dan halaman publik menggunakan perhitungan yang berbeda.

Kontrol Manual Admin
Walaupun status dibuat semi-otomatis, admin tetap membutuhkan kontrol manual untuk:

Membatalkan acara.
Menandai selesai pada agenda tanpa ends_at.
Mengoreksi data jika ada kondisi khusus.
Mengubah waktu acara.
UI dapat dibuat dengan penjelasan singkat seperti:

Status Terjadwal, Berlangsung, dan Selesai akan menyesuaikan waktu acara. Status Dibatalkan ditentukan secara manual.

Dengan penjelasan tersebut, admin memahami bahwa sebagian status dikendalikan sistem.

Validasi Tanggal yang Perlu Diperhatikan
Selain status, sebaiknya pastikan juga:

starts_at wajib diisi.
ends_at boleh kosong.
Jika ends_at diisi, nilainya harus setelah starts_at.
Status dibatalkan tetap dapat digunakan meskipun tanggal acara belum lewat.
Perubahan tanggal memicu perhitungan ulang status.
Contoh pesan validasi:

Waktu selesai harus lebih besar daripada waktu mulai.

Kesimpulan
Tidak ada perubahan kode yang dilakukan dalam pengecekan ini. Seluruh isi di atas merupakan hasil audit dan rekomendasi untuk bahan implementasi berikutnya.