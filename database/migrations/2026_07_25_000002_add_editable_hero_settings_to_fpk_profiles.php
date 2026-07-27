<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fpk_profiles', function (Blueprint $table) {
            $table->string('hero_eyebrow', 120)->nullable()->after('hero_subtitle');
            $table->string('hero_background_path')->nullable()->after('hero_eyebrow');
            $table->string('hero_primary_cta_label', 60)->nullable()->after('hero_background_path');
            $table->string('hero_secondary_cta_label', 60)->nullable()->after('hero_primary_cta_label');
            $table->string('hero_legal_basis_label', 60)->nullable()->after('hero_secondary_cta_label');
            $table->string('hero_foundation_label', 60)->nullable()->after('hero_legal_basis_label');
            $table->string('hero_period_label', 60)->nullable()->after('hero_foundation_label');
        });

        $organizationName = DB::table('site_settings')->value('organization_name')
            ?: 'Forum Pembauran Kebangsaan Kota Malang';

        DB::table('fpk_profiles')->update([
            'hero_eyebrow' => $organizationName,
            'hero_primary_cta_label' => 'Tentang FPK',
            'hero_secondary_cta_label' => 'Lihat Agenda',
            'hero_legal_basis_label' => 'Dasar Hukum',
            'hero_foundation_label' => 'Landasan',
            'hero_period_label' => 'Masa Bakti',
        ]);
    }

    public function down(): void
    {
        Schema::table('fpk_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'hero_eyebrow',
                'hero_background_path',
                'hero_primary_cta_label',
                'hero_secondary_cta_label',
                'hero_legal_basis_label',
                'hero_foundation_label',
                'hero_period_label',
            ]);
        });
    }
};
