<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('management_period_id')
                ->constrained('management_periods')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('position');
            $table->string('division')->nullable();
            $table->string('portrait_path')->nullable();
            $table->unsignedInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['management_period_id', 'is_active', 'display_order'], 'members_period_active_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_members');
    }
};
