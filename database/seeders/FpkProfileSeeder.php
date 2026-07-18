<?php

namespace Database\Seeders;

use App\Models\FpkProfile;
use Illuminate\Database\Seeder;

/**
 * Draft profile text retained from the source material used during initial setup.
 * Wording is safe to display, but the legal basis and period MUST be verified
 * against official documents before production (see TODO.md).
 */
class FpkProfileSeeder extends Seeder
{
    public function run(): void
    {
        if (FpkProfile::query()->exists()) {
            return;
        }

        FpkProfile::create([
            'hero_title' => 'Forum Pembauran Kebangsaan Kota Malang',
            'hero_subtitle' => 'Merawat kebhinnekaan, memperkuat persatuan warga Kota Malang.',
            'hero_image_path' => null,
            'definition' => 'Forum Pembauran Kebangsaan (FPK) Kota Malang merupakan wadah informasi, '
                .'komunikasi, konsultasi, dan kerja sama antarwarga masyarakat yang diarahkan untuk '
                .'menumbuhkan, memantapkan, memelihara, dan mengembangkan pembauran kebangsaan.',
            'background' => 'Bangsa Indonesia adalah bangsa yang majemuk dan plural. Kemajemukan tersebut '
                .'merupakan kekayaan sekaligus tanggung jawab bersama untuk dirawat dalam bingkai '
                ."Negara Kesatuan Republik Indonesia.\n\n"
                .'Dilandasi persamaan senasib seperjuangan dan semangat kebhinnekaan, diperlukan sebuah '
                .'wadah informasi, komunikasi, konsultasi, dan kerja sama antarwarga masyarakat agar '
                .'pembauran kebangsaan dapat tumbuh dan berkembang di tengah masyarakat.',
            'objectives' => "Menumbuhkan sikap toleransi dan saling menghormati.\n"
                ."Meningkatkan integrasi nasional.\n"
                ."Mencegah konflik sosial dan disintegrasi.\n"
                .'Membangun solidaritas dan persatuan.',
            'core_tasks' => "Menjaring aspirasi masyarakat terkait pembauran kebangsaan.\n"
                ."Menyelenggarakan dialog dengan organisasi kemasyarakatan, pemuka adat, suku, dan masyarakat.\n"
                ."Menyelenggarakan sosialisasi kebijakan pembauran kebangsaan.\n"
                .'Merumuskan rekomendasi kepada Wali Kota sebagai bahan kebijakan.',
            'legal_basis' => "Peraturan Gubernur Jawa Timur Nomor 41 Tahun 2009.\n"
                ."Surat Keputusan Wali Kota Malang Nomor 100.3.3.3/201/35.73.112/2025.\n"
                .'Perubahan atas Surat Keputusan Wali Kota Malang Nomor 100.3.3.3/130/35.73.112/2025 '
                .'tentang Pembentukan Forum Pembauran Kebangsaan dan Dewan Pembina Masa Bakti Tahun 2025-2027.',
        ]);
    }
}
