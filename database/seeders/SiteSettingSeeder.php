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
            'default_meta_title' => 'FPK Kota Malang - Forum Pembauran Kebangsaan',
            'default_meta_description' => 'Website resmi Forum Pembauran Kebangsaan (FPK) Kota Malang: '
                .'profil, artikel, agenda, susunan pengurus, dan kontak.',
            'default_meta_keywords' => 'FPK Kota Malang, pembauran kebangsaan, kerukunan, Kota Malang',
            'default_og_image_path' => null,
        ]);
    }
}
