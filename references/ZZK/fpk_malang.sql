-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for fpk_malang
CREATE DATABASE IF NOT EXISTS `fpk_malang` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `fpk_malang`;

-- Dumping structure for table fpk_malang.admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.admins: ~1 rows (approximately)
INSERT INTO `admins` (`id`, `name`, `email`, `password`, `is_active`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
	(2, 'Administrator PT Zam Zam Khan', 'admin@gmail.com', '$2y$12$IsvE/HPtHX4NE/Fjy0cV8ewMWyLQ0Mmdg./jF.vUbqnElSbJaEuMG', 1, '2026-07-06 16:11:29', NULL, '2026-07-02 20:26:08', '2026-07-06 16:11:29');

-- Dumping structure for table fpk_malang.articles
CREATE TABLE IF NOT EXISTS `articles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `article_category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(220) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_alt` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `meta_title` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articles_slug_unique` (`slug`),
  KEY `articles_slug_index` (`slug`),
  KEY `articles_status_index` (`status`),
  KEY `articles_published_at_index` (`published_at`),
  KEY `articles_article_category_id_index` (`article_category_id`),
  CONSTRAINT `articles_article_category_id_foreign` FOREIGN KEY (`article_category_id`) REFERENCES `article_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.articles: ~3 rows (approximately)
INSERT INTO `articles` (`id`, `article_category_id`, `title`, `slug`, `excerpt`, `content`, `cover_image`, `cover_alt`, `status`, `published_at`, `meta_title`, `meta_description`, `created_at`, `updated_at`) VALUES
	(1, 2, 'Perbedaan Sertifikat Halal Self Declare dan Reguler', 'perbedaan-sertifikat-halal-self-declare-dan-reguler', 'Kenali perbedaan mendasar antara jalur sertifikasi halal self declare dan reguler agar pelaku usaha dapat memilih skema yang paling sesuai dengan kondisi produknya.', 'Sertifikasi halal menjadi salah satu kebutuhan penting bagi pelaku usaha, khususnya di sektor makanan dan minuman. Secara umum, terdapat dua jalur yang sering ditemui, yaitu jalur self declare dan jalur reguler. Memahami perbedaan keduanya membantu pelaku usaha memilih skema yang paling sesuai dengan kondisi produk dan proses produksinya.\n\nJalur self declare umumnya ditujukan untuk produk dengan proses sederhana dan bahan yang sudah jelas kehalalannya. Skema ini menekankan pernyataan pelaku usaha atas kehalalan produk dengan pendampingan pihak yang berwenang. Karena karakteristiknya, jalur ini biasanya lebih ringkas untuk usaha berskala kecil.\n\nSementara itu, jalur reguler umumnya diperuntukkan bagi produk dengan proses yang lebih kompleks, seperti restoran, katering, kafe, hingga produksi skala besar. Pada jalur ini, pemeriksaan terhadap bahan dan proses produksi dilakukan secara lebih menyeluruh sesuai ketentuan yang berlaku.\n\nPemilihan jalur yang tepat sebaiknya disesuaikan dengan jenis produk, bahan yang digunakan, serta kapasitas usaha. Konsultasi awal dapat membantu pelaku usaha mengidentifikasi skema yang paling relevan sebelum memulai proses.\n\nPT Zam Zam Khan mendampingi pelaku usaha di Malang untuk memahami kebutuhan sertifikasi halal dan menyiapkan dokumen yang diperlukan sesuai ruang lingkup layanan.', NULL, NULL, 'published', '2026-07-05 17:11:34', 'Perbedaan Sertifikat Halal Self Declare dan Reguler', 'Panduan memahami perbedaan sertifikat halal self declare dan reguler untuk pelaku usaha dan UMKM di Malang.', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(2, 3, 'Panduan Pengurusan NIB untuk UMKM di Malang', 'panduan-pengurusan-nib-untuk-umkm-di-malang', 'Nomor Induk Berusaha (NIB) adalah identitas resmi pelaku usaha. Simak gambaran umum manfaat dan tahapan pendampingannya untuk UMKM.', 'Nomor Induk Berusaha atau NIB merupakan identitas resmi bagi pelaku usaha sekaligus dasar legalitas dalam menjalankan kegiatan usaha. Bagi UMKM, keberadaan NIB memberikan kejelasan status usaha dan mempermudah akses terhadap berbagai kebutuhan administrasi selanjutnya.\n\nSecara umum, pengurusan NIB dimulai dari penyiapan data usaha dan dokumen dasar pelaku usaha. Kelengkapan data ini penting agar identitas usaha yang tercatat sesuai dengan kondisi sebenarnya. Setelah data siap, proses pengajuan dapat dilanjutkan sesuai ketentuan yang berlaku.\n\nBagi pelaku usaha yang baru memulai, tahap identifikasi kebutuhan menjadi langkah awal yang membantu menentukan bentuk usaha dan ruang lingkup kegiatan. Dengan begitu, legalitas yang dimiliki dapat menyesuaikan rencana pengembangan usaha ke depan.\n\nMemiliki NIB juga menjadi bagian dari upaya membuat usaha lebih tertib secara administratif. Hal ini dapat mendukung kepercayaan mitra maupun pelanggan terhadap usaha yang dijalankan.\n\nPT Zam Zam Khan membantu pendampingan pengurusan NIB bagi UMKM dan pelaku usaha di Malang, mulai dari identifikasi kebutuhan hingga penyiapan dokumen sesuai ruang lingkup layanan.', NULL, NULL, 'published', '2026-07-02 17:11:34', 'Panduan Pengurusan NIB untuk UMKM di Malang', 'Gambaran umum manfaat dan tahapan pengurusan NIB untuk UMKM serta pelaku usaha di Malang.', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(3, 5, 'Mengapa Merek Dagang dan HAKI Penting untuk Usaha Anda', 'mengapa-merek-dagang-dan-haki-penting-untuk-usaha-anda', 'Perlindungan merek dan hak kekayaan intelektual membantu menjaga identitas serta nilai usaha Anda dalam jangka panjang.', 'Merek dagang merupakan salah satu identitas yang membedakan produk atau layanan sebuah usaha dari yang lain. Seiring pertumbuhan usaha, merek menjadi aset yang memiliki nilai tersendiri karena melekat pada reputasi dan kepercayaan pelanggan.\n\nPerlindungan terhadap merek melalui hak kekayaan intelektual (HAKI) membantu menjaga identitas usaha agar tidak digunakan pihak lain tanpa hak. Langkah ini penting sebagai bagian dari strategi menjaga keberlanjutan dan nilai usaha dalam jangka panjang.\n\nSelain merek, HAKI juga dapat mencakup perlindungan atas karya, desain, maupun aset intelektual lain yang dimiliki usaha. Dengan memahami cakupan ini, pelaku usaha dapat menentukan aset mana yang perlu diprioritaskan untuk dilindungi.\n\nProses pendaftaran umumnya diawali dengan identifikasi aset yang akan dilindungi dan penyiapan dokumen pendukung. Konsultasi awal membantu pelaku usaha memahami langkah yang paling sesuai dengan kebutuhannya.\n\nPT Zam Zam Khan mendampingi pelaku usaha di Malang dalam memahami kebutuhan perlindungan merek dan HAKI serta menyiapkan dokumen sesuai ruang lingkup layanan.', NULL, NULL, 'published', '2026-06-29 17:11:34', 'Mengapa Merek Dagang dan HAKI Penting untuk Usaha', 'Alasan pentingnya perlindungan merek dagang dan HAKI bagi pelaku usaha, serta pendampingannya di Malang.', '2026-07-05 17:11:34', '2026-07-05 17:11:34');

-- Dumping structure for table fpk_malang.article_categories
CREATE TABLE IF NOT EXISTS `article_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(140) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.article_categories: ~7 rows (approximately)
INSERT INTO `article_categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
	(1, 'Aktivitas Perusahaan', 'aktivitas-perusahaan', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(2, 'Sertifikasi Halal', 'sertifikasi-halal', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(3, 'Legalitas Usaha', 'legalitas-usaha', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(4, 'BPOM', 'bpom', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(5, 'HAKI', 'haki', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(6, 'Perpajakan', 'perpajakan', '2026-07-05 17:11:34', '2026-07-05 17:11:34'),
	(7, 'Branding & Kemasan', 'branding-kemasan', '2026-07-05 17:11:34', '2026-07-05 17:11:34');

-- Dumping structure for table fpk_malang.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.cache: ~1 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-site_content_v1', 'a:22:{s:4:"name";s:15:"PT Zam Zam Khan";s:5:"brand";s:12:"Zam Zam Khan";s:7:"tagline";s:24:"Bisnis & Legal Konsultan";s:4:"city";s:6:"Malang";s:13:"phone_display";s:15:"085-234-797-788";s:9:"phone_raw";s:12:"085234797788";s:5:"email";s:23:"pt.zamzamkhan@gmail.com";s:7:"address";s:85:"Jl. MT. Haryono Gang 8B No.949, Dinoyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65144";s:8:"maps_url";s:134:"https://www.google.com/maps/search/?api=1&query=Jl.%20MT.%20Haryono%20Gang%208B%20No.949%2C%20Dinoyo%2C%20Lowokwaru%2C%20Kota%20Malang";s:10:"maps_embed";s:146:"https://www.google.com/maps?q=Jl.%20MT%20Haryono%20Gang%208B%20No.949%2C%20Dinoyo%2C%20Lowokwaru%2C%20Kota%20Malang%2C%20Jawa%20Timur&output=embed";s:8:"whatsapp";s:124:"https://wa.me/6285234797788?text=Halo%20PT%20Zam%20Zam%20Khan%2C%20saya%20ingin%20berkonsultasi%20mengenai%20layanan%20Anda.";s:7:"socials";a:3:{i:0;a:3:{s:5:"label";s:9:"Instagram";s:6:"handle";s:9:"Instagram";s:3:"url";s:35:"https://instagram.com/pt.zamzamkhan";}i:1;a:3:{s:5:"label";s:8:"Facebook";s:6:"handle";s:8:"Facebook";s:3:"url";s:21:"https://facebook.com/";}i:2;a:3:{s:5:"label";s:6:"TikTok";s:6:"handle";s:6:"TikTok";s:3:"url";s:33:"https://tiktok.com/@pt.zamzamkhan";}}s:3:"nav";a:6:{i:0;a:2:{s:5:"label";s:7:"Tentang";s:6:"anchor";s:8:"#tentang";}i:1;a:2:{s:5:"label";s:7:"Layanan";s:6:"anchor";s:8:"#layanan";}i:2;a:2:{s:5:"label";s:4:"Alur";s:6:"anchor";s:5:"#alur";}i:3;a:2:{s:5:"label";s:10:"Keunggulan";s:6:"anchor";s:11:"#keunggulan";}i:4;a:2:{s:5:"label";s:3:"FAQ";s:6:"anchor";s:4:"#faq";}i:5;a:2:{s:5:"label";s:6:"Kontak";s:6:"anchor";s:7:"#kontak";}}s:8:"services";a:8:{i:0;a:3:{s:4:"icon";s:5:"halal";s:5:"title";s:16:"Sertifikat Halal";s:4:"desc";s:140:"Pendampingan proses sertifikasi halal untuk membantu pelaku usaha memenuhi kebutuhan jaminan kehalalan produk sesuai ketentuan yang berlaku.";}i:1;a:3:{s:4:"icon";s:9:"halal-reg";s:5:"title";s:24:"Sertifikat Halal Reguler";s:4:"desc";s:151:"Pendampingan sertifikat halal reguler untuk usaha di luar kategori self declare, seperti restoran, catering, cafe, pabrik produksi besar, RPH, dan RPU.";}i:2;a:3:{s:4:"icon";s:3:"nib";s:5:"title";s:28:"NIB — Nomor Induk Berusaha";s:4:"desc";s:116:"Pendampingan pembuatan Nomor Induk Berusaha sebagai identitas resmi pelaku usaha dan dasar legalitas kegiatan usaha.";}i:3;a:3:{s:4:"icon";s:4:"akta";s:5:"title";s:26:"Akta Pendirian Badan Usaha";s:4:"desc";s:119:"Pendampingan kebutuhan akta pendirian untuk badan usaha seperti PT, CV, firma, atau bentuk usaha lain sesuai kebutuhan.";}i:4;a:3:{s:4:"icon";s:5:"pajak";s:5:"title";s:24:"NPWP dan Pelaporan Pajak";s:4:"desc";s:96:"Pendampingan administrasi NPWP dan pelaporan pajak agar kewajiban perpajakan usaha lebih tertib.";}i:5;a:3:{s:4:"icon";s:4:"bpom";s:5:"title";s:4:"BPOM";s:4:"desc";s:130:"Pendampingan informasi dan proses awal terkait izin edar BPOM untuk produk yang membutuhkan legalitas distribusi sesuai ketentuan.";}i:6;a:3:{s:4:"icon";s:4:"haki";s:5:"title";s:4:"HAKI";s:4:"desc";s:117:"Pendampingan pendaftaran hak kekayaan intelektual untuk melindungi karya, merek, desain, atau aset intelektual usaha.";}i:7;a:3:{s:4:"icon";s:6:"desain";s:5:"title";s:27:"Desain Logo & Label Kemasan";s:4:"desc";s:117:"Jasa desain logo dan label kemasan produk untuk mendukung branding, identitas visual, dan kebutuhan pemasaran produk.";}}s:7:"process";a:6:{i:0;a:2:{s:5:"title";s:15:"Konsultasi Awal";s:4:"desc";s:67:"Diskusi kebutuhan usaha dan tujuan legalitas atau sertifikasi Anda.";}i:1;a:2:{s:5:"title";s:22:"Identifikasi Kebutuhan";s:4:"desc";s:65:"Menentukan jenis layanan yang paling sesuai dengan kondisi usaha.";}i:2;a:2:{s:5:"title";s:17:"Persiapan Dokumen";s:4:"desc";s:62:"Pengecekan dan penyiapan dokumen yang diperlukan untuk proses.";}i:3;a:2:{s:5:"title";s:18:"Pengajuan & Proses";s:4:"desc";s:70:"Pengajuan atau pendampingan administrasi sesuai ruang lingkup layanan.";}i:4;a:2:{s:5:"title";s:17:"Monitoring Proses";s:4:"desc";s:57:"Pemantauan perkembangan proses hingga tahap penyelesaian.";}i:5;a:2:{s:5:"title";s:21:"Hasil & Tindak Lanjut";s:4:"desc";s:68:"Penyerahan hasil akhir beserta arahan tindak lanjut bagi usaha Anda.";}}s:10:"advantages";a:5:{i:0;s:80:"Pendampingan dari tahap awal hingga proses selesai sesuai ruang lingkup layanan.";i:1;s:87:"Cocok untuk UMKM, restoran, catering, cafe, produsen makanan, dan pelaku usaha lainnya.";i:2;s:55:"Informasi layanan disampaikan secara jelas dan terarah.";i:3;s:70:"Membantu usaha menjadi lebih tertib secara legalitas dan administrasi.";i:4;s:87:"Mendukung peningkatan nilai jual melalui legalitas, sertifikasi, dan identitas kemasan.";}s:5:"stats";a:4:{i:0;a:2:{s:5:"value";s:2:"8+";s:5:"label";s:26:"Jenis Layanan Pendampingan";}i:1;a:2:{s:5:"value";s:4:"100%";s:5:"label";s:20:"Pendampingan Terarah";}i:2;a:2:{s:5:"value";s:4:"UMKM";s:5:"label";s:24:"Hingga Skala Usaha Besar";}i:3;a:2:{s:5:"value";s:6:"Malang";s:5:"label";s:24:"Basis Layanan Konsultasi";}}s:7:"gallery";a:6:{i:0;a:2:{s:3:"img";s:13:"nib-halal.png";s:3:"alt";s:61:"Layanan pendampingan NIB dan sertifikat halal PT Zam Zam Khan";}i:1;a:2:{s:3:"img";s:13:"npwp-bpom.png";s:3:"alt";s:48:"Layanan pengurusan NPWP dan BPOM PT Zam Zam Khan";}i:2;a:2:{s:3:"img";s:17:"services-akta.png";s:3:"alt";s:63:"Layanan pendampingan akta pendirian badan usaha PT Zam Zam Khan";}i:3;a:2:{s:3:"img";s:16:"haki-contact.png";s:3:"alt";s:40:"Layanan pendaftaran HAKI PT Zam Zam Khan";}i:4;a:2:{s:3:"img";s:25:"brochure-design-halal.png";s:3:"alt";s:58:"Desain label kemasan dan sertifikasi halal PT Zam Zam Khan";}i:5;a:2:{s:3:"img";s:18:"profile-atfiah.png";s:3:"alt";s:58:"Profil layanan konsultasi bisnis dan legal PT Zam Zam Khan";}}s:3:"faq";a:5:{i:0;a:2:{s:1:"q";s:55:"Apakah PT Zam Zam Khan hanya melayani sertifikat halal?";s:1:"a";s:154:"Tidak. Selain sertifikat halal, PT Zam Zam Khan juga melayani legalitas usaha, NIB, akta pendirian, NPWP, BPOM, HAKI, serta desain logo dan label kemasan.";}i:1;a:2:{s:1:"q";s:31:"Apakah UMKM bisa berkonsultasi?";s:1:"a";s:89:"Ya. Layanan dapat disesuaikan dengan kebutuhan UMKM maupun pelaku usaha yang lebih besar.";}i:2;a:2:{s:1:"q";s:66:"Apakah bisa konsultasi terlebih dahulu sebelum menentukan layanan?";s:1:"a";s:97:"Ya. Calon klien dapat menghubungi kontak resmi untuk menjelaskan kebutuhan usaha terlebih dahulu.";}i:3;a:2:{s:1:"q";s:61:"Apakah tersedia pendampingan untuk usaha makanan dan minuman?";s:1:"a";s:141:"Ya. Layanan mencakup pendampingan untuk pelaku usaha makanan dan minuman, termasuk kebutuhan halal, BPOM, label kemasan, dan legalitas usaha.";}i:4;a:2:{s:1:"q";s:43:"Bagaimana cara menghubungi PT Zam Zam Khan?";s:1:"a";s:121:"Calon klien dapat menghubungi melalui WhatsApp, email, atau datang langsung ke alamat kantor yang tercantum pada website.";}}s:4:"hero";a:6:{s:5:"title";s:45:"Konsultan Halal dan Legalitas Usaha di Malang";s:8:"subtitle";s:170:"PT Zam Zam Khan membantu pelaku usaha dalam pendampingan sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, serta desain logo dan label kemasan produk.";s:12:"primary_text";s:23:"Konsultasi via WhatsApp";s:11:"primary_url";s:124:"https://wa.me/6285234797788?text=Halo%20PT%20Zam%20Zam%20Khan%2C%20saya%20ingin%20berkonsultasi%20mengenai%20layanan%20Anda.";s:14:"secondary_text";s:13:"Lihat Layanan";s:13:"secondary_url";s:8:"#layanan";}s:3:"seo";a:7:{s:5:"title";s:61:"Konsultan Halal & Legalitas Usaha di Malang | PT Zam Zam Khan";s:11:"description";s:164:"PT Zam Zam Khan melayani konsultasi halal, legalitas usaha, NIB, akta pendirian, NPWP, BPOM, HAKI, dan desain label kemasan untuk UMKM serta pelaku usaha di Malang.";s:8:"keywords";s:139:"konsultan halal Malang, jasa sertifikat halal Malang, konsultan legalitas usaha Malang, jasa BPOM Malang, jasa NIB Malang, jasa HAKI Malang";s:8:"og_title";s:44:"PT Zam Zam Khan — Bisnis & Legal Konsultan";s:14:"og_description";s:86:"Pendampingan halal, legalitas usaha, dan branding produk untuk pelaku usaha di Malang.";s:8:"og_image";s:41:"http://127.0.0.1:8230/images/logo-zzk.png";s:9:"canonical";N;}s:13:"gallery_items";a:0:{}}', 1783087657);

-- Dumping structure for table fpk_malang.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.cache_locks: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.faqs
CREATE TABLE IF NOT EXISTS `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.faqs: ~5 rows (approximately)
INSERT INTO `faqs` (`id`, `question`, `answer`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Apakah PT Zam Zam Khan hanya melayani sertifikat halal?', 'Tidak. Selain sertifikat halal, PT Zam Zam Khan juga melayani legalitas usaha, NIB, akta pendirian, NPWP, BPOM, HAKI, serta desain logo dan label kemasan.', 0, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(2, 'Apakah UMKM bisa berkonsultasi?', 'Ya. Layanan dapat disesuaikan dengan kebutuhan UMKM maupun pelaku usaha yang lebih besar.', 1, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(3, 'Apakah bisa konsultasi terlebih dahulu sebelum menentukan layanan?', 'Ya. Calon klien dapat menghubungi kontak resmi untuk menjelaskan kebutuhan usaha terlebih dahulu.', 2, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(4, 'Apakah tersedia pendampingan untuk usaha makanan dan minuman?', 'Ya. Layanan mencakup pendampingan untuk pelaku usaha makanan dan minuman, termasuk kebutuhan halal, BPOM, label kemasan, dan legalitas usaha.', 3, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(5, 'Bagaimana cara menghubungi PT Zam Zam Khan?', 'Calon klien dapat menghubungi melalui WhatsApp, email, atau datang langsung ke alamat kantor yang tercantum pada website.', 4, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37');

-- Dumping structure for table fpk_malang.galleries
CREATE TABLE IF NOT EXISTS `galleries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.galleries: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.hero_sections
CREATE TABLE IF NOT EXISTS `hero_sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` text COLLATE utf8mb4_unicode_ci,
  `primary_button_text` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_button_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_button_text` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_button_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `badge_text` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trust_text` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_chips` text COLLATE utf8mb4_unicode_ci,
  `portrait_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portrait_alt` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portrait_role` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `portrait_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.hero_sections: ~1 rows (approximately)
INSERT INTO `hero_sections` (`id`, `title`, `subtitle`, `primary_button_text`, `primary_button_url`, `secondary_button_text`, `secondary_button_url`, `image_path`, `badge_text`, `trust_text`, `service_chips`, `portrait_path`, `portrait_alt`, `portrait_role`, `portrait_name`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Konsultan Halal dan Legalitas Usaha di Malang', 'PT Zam Zam Khan membantu pelaku usaha dalam pendampingan sertifikasi halal, legalitas usaha, BPOM, HAKI, NPWP, akta pendirian, serta desain logo dan label kemasan produk.', 'Konsultasi via WhatsApp', 'https://wa.me/6285234797788?text=Halo%20PT%20Zam%20Zam%20Khan%2C%20saya%20ingin%20berkonsultasi%20mengenai%20layanan%20Anda.', 'Lihat Layanan', '#layanan', 'images/hero-consulting.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-07-02 20:12:49', '2026-07-05 23:48:42');

-- Dumping structure for table fpk_malang.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.jobs: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.job_batches: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.messages
CREATE TABLE IF NOT EXISTS `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_interest` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.messages: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.migrations: ~9 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_07_01_100000_create_cms_tables', 1),
	(5, '2026_07_06_120000_create_article_tables', 2),
	(6, '2026_07_06_140000_add_profile_fields_to_site_settings', 3),
	(7, '2026_07_06_150000_add_full_edit_fields_to_hero_sections', 3),
	(8, '2026_07_07_010000_add_social_links_to_site_settings', 4),
	(9, '2026_07_07_020000_add_detail_fields_to_services', 5);

-- Dumping structure for table fpk_malang.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.process_steps
CREATE TABLE IF NOT EXISTS `process_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.process_steps: ~6 rows (approximately)
INSERT INTO `process_steps` (`id`, `title`, `description`, `icon`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Konsultasi Awal', 'Diskusi kebutuhan usaha dan tujuan legalitas atau sertifikasi Anda.', NULL, 0, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(2, 'Identifikasi Kebutuhan', 'Menentukan jenis layanan yang paling sesuai dengan kondisi usaha.', NULL, 1, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(3, 'Persiapan Dokumen', 'Pengecekan dan penyiapan dokumen yang diperlukan untuk proses.', NULL, 2, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(4, 'Pengajuan & Proses', 'Pengajuan atau pendampingan administrasi sesuai ruang lingkup layanan.', NULL, 3, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(5, 'Monitoring Proses', 'Pemantauan perkembangan proses hingga tahap penyelesaian.', NULL, 4, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(6, 'Hasil & Tindak Lanjut', 'Penyerahan hasil akhir beserta arahan tindak lanjut bagi usaha Anda.', NULL, 5, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37');

-- Dumping structure for table fpk_malang.seo_settings
CREATE TABLE IF NOT EXISTS `seo_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `page_key` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_title` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `canonical_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seo_settings_page_key_unique` (`page_key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.seo_settings: ~1 rows (approximately)
INSERT INTO `seo_settings` (`id`, `page_key`, `meta_title`, `meta_description`, `meta_keywords`, `og_title`, `og_description`, `og_image_path`, `canonical_url`, `created_at`, `updated_at`) VALUES
	(1, 'home', 'Konsultan Halal & Legalitas Usaha di Malang | PT Zam Zam Khan', 'PT Zam Zam Khan melayani konsultasi halal, legalitas usaha, NIB, akta pendirian, NPWP, BPOM, HAKI, dan desain label kemasan untuk UMKM serta pelaku usaha di Malang.', 'konsultan halal Malang, jasa sertifikat halal Malang, konsultan legalitas usaha Malang, jasa BPOM Malang, jasa NIB Malang, jasa HAKI Malang', 'PT Zam Zam Khan — Bisnis & Legal Konsultan', 'Pendampingan halal, legalitas usaha, dan branding produk untuk pelaku usaha di Malang.', NULL, NULL, '2026-07-02 20:12:49', '2026-07-02 20:12:49');

-- Dumping structure for table fpk_malang.services
CREATE TABLE IF NOT EXISTS `services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `benefits` text COLLATE utf8mb4_unicode_ci,
  `suitable_for` text COLLATE utf8mb4_unicode_ci,
  `workflow_steps` text COLLATE utf8mb4_unicode_ci,
  `whatsapp_message` text COLLATE utf8mb4_unicode_ci,
  `display_order` int NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `services_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.services: ~8 rows (approximately)
INSERT INTO `services` (`id`, `title`, `slug`, `icon`, `summary`, `description`, `benefits`, `suitable_for`, `workflow_steps`, `whatsapp_message`, `display_order`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Sertifikat Halal', 'sertifikat-halal', 'halal', 'Pendampingan proses sertifikasi halal untuk membantu pelaku usaha memenuhi kebutuhan jaminan kehalalan produk sesuai ketentuan yang berlaku.', 'Pendampingan proses sertifikasi halal untuk membantu pelaku usaha memenuhi kebutuhan jaminan kehalalan produk sesuai ketentuan yang berlaku.', NULL, NULL, NULL, NULL, 0, 1, 1, '2026-07-02 20:12:49', '2026-07-03 01:07:37'),
	(2, 'Sertifikat Halal Reguler', 'sertifikat-halal-reguler', 'halal-reg', 'Pendampingan sertifikat halal reguler untuk usaha di luar kategori self declare, seperti restoran, catering, cafe, pabrik produksi besar, RPH, dan RPU...', 'Pendampingan sertifikat halal reguler untuk usaha di luar kategori self declare, seperti restoran, catering, cafe, pabrik produksi besar, RPH, dan RPU.', NULL, NULL, NULL, NULL, 1, 1, 1, '2026-07-02 20:12:49', '2026-07-02 20:12:49'),
	(3, 'NIB — Nomor Induk Berusaha', 'nib-nomor-induk-berusaha', 'nib', 'Pendampingan pembuatan Nomor Induk Berusaha sebagai identitas resmi pelaku usaha dan dasar legalitas kegiatan usaha.', 'Pendampingan pembuatan Nomor Induk Berusaha sebagai identitas resmi pelaku usaha dan dasar legalitas kegiatan usaha.', NULL, NULL, NULL, NULL, 2, 1, 1, '2026-07-02 20:12:49', '2026-07-02 20:12:49'),
	(4, 'Akta Pendirian Badan Usaha', 'akta-pendirian-badan-usaha', 'akta', 'Pendampingan kebutuhan akta pendirian untuk badan usaha seperti PT, CV, firma, atau bentuk usaha lain sesuai kebutuhan.', 'Pendampingan kebutuhan akta pendirian untuk badan usaha seperti PT, CV, firma, atau bentuk usaha lain sesuai kebutuhan.', NULL, NULL, NULL, NULL, 3, 1, 1, '2026-07-02 20:12:49', '2026-07-02 20:12:49'),
	(5, 'NPWP dan Pelaporan Pajak', 'npwp-dan-pelaporan-pajak', 'pajak', 'Pendampingan administrasi NPWP dan pelaporan pajak agar kewajiban perpajakan usaha lebih tertib.', 'Pendampingan administrasi NPWP dan pelaporan pajak agar kewajiban perpajakan usaha lebih tertib.', NULL, NULL, NULL, NULL, 4, 0, 1, '2026-07-02 20:12:49', '2026-07-02 20:12:49'),
	(6, 'BPOM', 'bpom', 'bpom', 'Pendampingan informasi dan proses awal terkait izin edar BPOM untuk produk yang membutuhkan legalitas distribusi sesuai ketentuan.', 'Pendampingan informasi dan proses awal terkait izin edar BPOM untuk produk yang membutuhkan legalitas distribusi sesuai ketentuan.', NULL, NULL, NULL, NULL, 5, 0, 1, '2026-07-02 20:12:49', '2026-07-02 20:12:49'),
	(7, 'HAKI', 'haki', 'haki', 'Pendampingan pendaftaran hak kekayaan intelektual untuk melindungi karya, merek, desain, atau aset intelektual usaha.', 'Pendampingan pendaftaran hak kekayaan intelektual untuk melindungi karya, merek, desain, atau aset intelektual usaha.', NULL, NULL, NULL, NULL, 6, 0, 1, '2026-07-02 20:12:49', '2026-07-02 20:12:49'),
	(8, 'Desain Logo & Label Kemasan', 'desain-logo-label-kemasan', 'desain', 'Jasa desain logo dan label kemasan produk untuk mendukung branding, identitas visual, dan kebutuhan pemasaran produk.', 'Jasa desain logo dan label kemasan produk untuk mendukung branding, identitas visual, dan kebutuhan pemasaran produk.', NULL, NULL, NULL, NULL, 7, 0, 1, '2026-07-02 20:12:49', '2026-07-02 20:12:49');

-- Dumping structure for table fpk_malang.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.sessions: ~0 rows (approximately)

-- Dumping structure for table fpk_malang.site_settings
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PT Zam Zam Khan',
  `brand_name` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `consultant_name` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tagline` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_description` text COLLATE utf8mb4_unicode_ci,
  `vision` text COLLATE utf8mb4_unicode_ci,
  `mission` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `operating_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maps_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `maps_embed_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `facebook_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `social_links` json DEFAULT NULL,
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.site_settings: ~1 rows (approximately)
INSERT INTO `site_settings` (`id`, `company_name`, `brand_name`, `consultant_name`, `tagline`, `company_description`, `vision`, `mission`, `phone`, `whatsapp`, `email`, `address`, `operating_hours`, `maps_url`, `maps_embed_url`, `facebook_url`, `instagram_url`, `tiktok_url`, `social_links`, `logo_path`, `favicon_path`, `created_at`, `updated_at`) VALUES
	(1, 'PT Zam Zam Khan', 'Zam Zam Khan Bisnis & Legal Konsultan', NULL, 'Bisnis & Legal Konsultan', NULL, NULL, NULL, '085234797788', '6285234797788', 'pt.zamzamkhan@gmail.com', 'Jl. MT. Haryono Gang 6B No.949, Dinoyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65144', NULL, NULL, NULL, 'https://facebook.com/', 'https://instagram.com/pt.zamzamkhan', 'https://tiktok.com/@pt.zamzamkhan', NULL, 'images/logo-zzk.png', NULL, '2026-07-02 20:12:49', '2026-07-05 01:33:24');

-- Dumping structure for table fpk_malang.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table fpk_malang.users: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
