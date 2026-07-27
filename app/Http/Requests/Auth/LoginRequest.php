<?php

namespace App\Http\Requests\Auth;

use App\Support\AdminActivityLogger;
use App\Support\AdminDeviceIdentity;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const LOCKOUT_SECONDS = 3600;

    private const MAX_ATTEMPTS = 3;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'))) {
            foreach ($this->throttleKeys() as $key) {
                RateLimiter::hit($key, self::LOCKOUT_SECONDS);
            }

            app(AdminActivityLogger::class)->log(
                $this,
                'auth.login_failed',
                'Percobaan login admin gagal.',
                statusCode: 422,
            );

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        foreach ($this->throttleKeys() as $key) {
            RateLimiter::clear($key);
        }
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $blocked = collect($this->throttleKeys())
            ->contains(fn (string $key): bool => RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS));

        if (! $blocked) {
            return;
        }

        event(new Lockout($this));
        app(AdminActivityLogger::class)->log(
            $this,
            'auth.login_blocked',
            'Percobaan login admin ditolak karena IP atau perangkat sedang diblokir.',
            statusCode: 422,
        );

        throw ValidationException::withMessages([
            'email' => __('auth.failed'),
        ]);
    }

    /**
     * @return array{string, string}
     */
    public function throttleKeys(): array
    {
        $identity = app(AdminDeviceIdentity::class);

        return [
            'admin-login:ip:'.$identity->ipKey($this),
            'admin-login:device:'.$identity->key($this),
        ];
    }
}
