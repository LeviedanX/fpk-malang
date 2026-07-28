<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Optionally provisions the first administrator from explicit environment
 * values. It never supplies defaults or resets an existing account's password.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('admin.initial.name'));
        $email = mb_strtolower(trim((string) config('admin.initial.email')));
        $password = (string) config('admin.initial.password');

        if ($name === '' || $email === '' || $password === '') {
            if (app()->environment('production')) {
                throw new RuntimeException(
                    'ADMIN_NAME, ADMIN_EMAIL, dan ADMIN_PASSWORD wajib diisi untuk provisioning admin produksi.'
                );
            }

            $this->command?->warn(
                'Admin tidak dibuat. Jalankan php artisan admin:create atau isi ADMIN_* secara eksplisit.'
            );

            return;
        }

        if (User::query()->where('email', $email)->exists()) {
            $this->command?->warn('Admin sudah ada; password tidak diubah oleh seeder.');

            return;
        }

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);
    }
}
