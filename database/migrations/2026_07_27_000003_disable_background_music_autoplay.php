<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('site_settings')
            ->where('background_music_default_playing', true)
            ->update([
                'background_music_default_playing' => false,
                'background_music_preference_version' => DB::raw('background_music_preference_version + 1'),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Tidak mengaktifkan kembali autoplay saat rollback karena perubahan ini
        // merupakan hardening UX, bukan transformasi data yang perlu dibalik.
    }
};
