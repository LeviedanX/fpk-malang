<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('background_music_visible')
                ->default(true)
                ->after('background_music_path');
            $table->boolean('background_music_default_playing')
                ->default(true)
                ->after('background_music_visible');
            $table->unsignedTinyInteger('background_music_volume')
                ->default(50)
                ->after('background_music_default_playing');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'background_music_visible',
                'background_music_default_playing',
                'background_music_volume',
            ]);
        });
    }
};
