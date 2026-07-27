<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminDeviceIdentity
{
    public const COOKIE_NAME = 'admin_device_id';

    public function id(Request $request): string
    {
        $cookieId = (string) $request->cookie(self::COOKIE_NAME, '');

        if (Str::isUuid($cookieId)) {
            return $cookieId;
        }

        return hash_hmac(
            'sha256',
            mb_substr((string) $request->userAgent(), 0, 512),
            (string) config('app.key'),
        );
    }

    public function newId(): string
    {
        return (string) Str::uuid();
    }

    public function key(Request $request): string
    {
        return hash_hmac('sha256', $this->id($request), (string) config('app.key'));
    }

    public function ipKey(Request $request): string
    {
        return hash_hmac('sha256', (string) $request->ip(), (string) config('app.key'));
    }
}
