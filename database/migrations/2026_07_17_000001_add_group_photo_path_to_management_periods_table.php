<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('management_periods', function (Blueprint $table) {
            $table->string('group_photo_path')->nullable()->after('end_year');
        });
    }

    public function down(): void
    {
        Schema::table('management_periods', function (Blueprint $table) {
            $table->dropColumn('group_photo_path');
        });
    }
};
