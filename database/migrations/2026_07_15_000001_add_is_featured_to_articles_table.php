<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->after('thumbnail_path');

            // Speeds up "latest featured published" lookups on the homepage.
            $table->index(['is_featured', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['is_featured', 'status', 'published_at']);
            $table->dropColumn('is_featured');
        });
    }
};
