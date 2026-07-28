<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {--name= : Nama administrator}
        {--email= : Email administrator}
        {--from-env : Gunakan ADMIN_NAME, ADMIN_EMAIL, dan ADMIN_PASSWORD}';

    protected $description = 'Membuat akun administrator secara eksplisit tanpa kredensial bawaan';

    public function handle(): int
    {
        $fromEnvironment = (bool) $this->option('from-env');
        $name = $fromEnvironment
            ? (string) config('admin.initial.name')
            : (string) ($this->option('name') ?: $this->ask('Nama administrator'));
        $email = $fromEnvironment
            ? (string) config('admin.initial.email')
            : (string) ($this->option('email') ?: $this->ask('Email administrator'));
        $password = $fromEnvironment
            ? (string) config('admin.initial.password')
            : (string) $this->secret('Password administrator');

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'name' => $name,
            'email' => mb_strtolower($email),
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info('Akun administrator berhasil dibuat.');

        return self::SUCCESS;
    }
}
