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
    private const LOCKOUT_SECONDS = 900;

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
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
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
            foreach ($this->throttleLimits() as $limit) {
                RateLimiter::hit($limit['key'], $limit['decay']);
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

        RateLimiter::clear($this->accountThrottleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $blocked = collect($this->throttleLimits())
            ->contains(fn (array $limit): bool => RateLimiter::tooManyAttempts(
                $limit['key'],
                $limit['attempts'],
            ));

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
     * @return list<array{key: string, attempts: int, decay: int}>
     */
    public function throttleLimits(): array
    {
        $identity = app(AdminDeviceIdentity::class);

        return [
            ['key' => $this->accountThrottleKey(), 'attempts' => 5, 'decay' => self::LOCKOUT_SECONDS],
            ['key' => 'admin-login:device:'.$identity->key($this), 'attempts' => 10, 'decay' => self::LOCKOUT_SECONDS],
            ['key' => 'admin-login:ip:'.$identity->ipKey($this), 'attempts' => 20, 'decay' => self::LOCKOUT_SECONDS],
        ];
    }

    private function accountThrottleKey(): string
    {
        $identity = app(AdminDeviceIdentity::class);
        $email = mb_strtolower(trim((string) $this->input('email')));

        return 'admin-login:account:'.hash('sha256', $email.'|'.$identity->ipKey($this));
    }
}
