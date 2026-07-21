<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('default_meta_keywords', 500)
                ->nullable()
                ->after('default_meta_description');
        });

        Schema::table('fpk_profiles', function (Blueprint $table) {
            $table->string('about_image_path')->nullable()->after('hero_image_path');
            $table->string('institution_legal_basis', 120)->nullable()->after('about_image_path');
            $table->string('institution_foundation', 120)->nullable()->after('institution_legal_basis');
        });

        // Preserve the information previously rendered directly in the hero.
        DB::table('fpk_profiles')
            ->whereNull('institution_legal_basis')
            ->update(['institution_legal_basis' => 'Pergub Jatim No. 41/2009']);

        DB::table('fpk_profiles')
            ->whereNull('institution_foundation')
            ->update(['institution_foundation' => 'SK Wali Kota Malang']);
    }

    public function down(): void
    {
        Schema::table('fpk_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'about_image_path',
                'institution_legal_basis',
                'institution_foundation',
            ]);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn('default_meta_keywords');
        });
    }
};
