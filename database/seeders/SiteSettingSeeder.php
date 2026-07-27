<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        if (SiteSetting::query()->exists()) {
            return;
        }

        SiteSetting::create([
            'site_name' => 'FPK Kota Malang',
            'organization_name' => 'Forum Pembauran Kebangsaan Kota Malang',
            'abbreviation' => 'FPK Kota Malang',
            'tagline' => 'Merawat Kebhinnekaan, Memperkuat Persatuan',
            'logo_path' => null,
            'favicon_path' => null,
            'footer_text' => 'Forum Pembauran Kebangsaan Kota Malang',
            'default_meta_title' => 'FPK Kota Malang | Forum Pembauran Kebangsaan',
            'default_meta_description' => 'Situs resmi FPK Kota Malang yang menyajikan profil organisasi, program pembauran kebangsaan, '
                .'agenda kegiatan, artikel, susunan pengurus, dan kontak.',
            'default_meta_keywords' => 'FPK Kota Malang, Forum Pembauran Kebangsaan Kota Malang, '
                .'pembauran kebangsaan, kerukunan masyarakat, keberagaman Kota Malang, agenda FPK, artikel FPK',
            'default_og_image_path' => null,
            'admin_login_background_path' => null,
            'background_music_path' => null,
            'background_music_visible' => true,
            'background_music_default_playing' => true,
            'background_music_volume' => 50,
            'background_music_preference_version' => 1,
        ]);
    }
}
