<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fpk_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('hero_title');
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->text('definition')->nullable();
            $table->text('background')->nullable();
            $table->text('objectives')->nullable();
            $table->text('core_tasks')->nullable();
            $table->text('legal_basis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fpk_profiles');
    }
};
