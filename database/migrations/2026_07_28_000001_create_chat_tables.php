<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();

            // Kredensial rahasia milik tamu. Disimpan di localStorage browser
            // dan dikirim lewat header X-Chat-Token; satu-satunya kunci akses
            // ke percakapan sehingga halaman publik tetap bebas sesi/cookie.
            $table->char('public_token', 64)->unique();

            // Sidik jari pelacakan: sha256(app_key + IP + user agent). Dipakai
            // admin untuk mengelompokkan percakapan dari perangkat yang sama
            // tanpa pernah dijadikan kunci akses (dua tamu di satu kantor bisa
            // memiliki hash sama, jadi tidak boleh membuka percakapan mereka).
            $table->char('visitor_hash', 64);
            $table->string('guest_label', 40);

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('device_type', 16)->default('unknown');
            $table->string('browser_name', 40)->nullable();
            $table->string('platform_name', 40)->nullable();

            $table->string('status', 12)->default('open');
            $table->boolean('is_blocked')->default(false);

            $table->unsignedInteger('admin_unread_count')->default(0);
            $table->unsignedInteger('guest_unread_count')->default(0);
            $table->unsignedInteger('messages_count')->default(0);

            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_guest_message_at')->nullable();
            $table->timestamps();

            // Urutan inbox admin: percakapan teraktif lebih dulu.
            $table->index(['status', 'last_message_at']);
            // Pengelompokan "perangkat yang sama" pada panel admin.
            $table->index('visitor_hash');
            // Lencana belum dibaca dihitung sekali per halaman admin.
            $table->index('admin_unread_count');
            // Dipakai perintah pembersihan berkala.
            $table->index('last_message_at');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('chat_conversations')
                ->cascadeOnDelete();

            $table->string('sender', 8);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedSmallInteger('attachment_width')->nullable();
            $table->unsignedSmallInteger('attachment_height')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Indeks utama polling: WHERE conversation_id = ? AND id > ?
            // ORDER BY id. Seluruh polling terlayani dari indeks ini.
            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
