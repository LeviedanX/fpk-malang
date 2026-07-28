<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Derives a stable, privacy-preserving identity for anonymous chat guests from
 * their IP address and user agent.
 *
 * The hash groups conversations that come from the same device on the admin
 * side; it is deliberately NOT usable as a credential, because two visitors
 * behind one office NAT with the same browser produce the same hash. Access to
 * a conversation always requires its random public token.
 */
class VisitorIdentity
{
    /**
     * @return array{
     *     visitor_hash: string,
     *     guest_label: string,
     *     ip_address: string|null,
     *     user_agent: string|null,
     *     device_type: string,
     *     browser_name: string|null,
     *     platform_name: string|null,
     * }
     */
    public static function fromRequest(Request $request): array
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent() === null
            ? null
            : mb_substr($request->userAgent(), 0, 512);

        $hash = hash_hmac(
            'sha256',
            ($ip ?? 'unknown')."\0".($userAgent ?? 'unknown'),
            (string) config('app.key'),
        );

        return [
            'visitor_hash' => $hash,
            'guest_label' => self::label($hash),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'device_type' => self::deviceType($userAgent),
            'browser_name' => self::browserName($userAgent),
            'platform_name' => self::platformName($userAgent),
        ];
    }

    /**
     * Short, human-friendly handle shown to the admin, e.g. "Tamu 7F3A".
     * Deterministic per device so returning visitors keep the same name.
     */
    public static function label(string $visitorHash): string
    {
        return 'Tamu '.mb_strtoupper(mb_substr($visitorHash, 0, 4));
    }

    /**
     * Classify a user agent as desktop / mobile / tablet / bot.
     *
     * Public because the admin desktop-only gate reuses exactly this rule set:
     * one definition of "mobile" keeps the gate and the chat tracking labels
     * from ever disagreeing.
     */
    public static function deviceType(?string $userAgent): string
    {
        if ($userAgent === null || $userAgent === '') {
            return 'unknown';
        }

        return match (true) {
            (bool) preg_match('/bot|crawler|spider|crawling|headless/i', $userAgent) => 'bot',
            (bool) preg_match('/iPad|Tablet|PlayBook|Silk|(Android(?!.*Mobile))/i', $userAgent) => 'tablet',
            (bool) preg_match('/Mobi|Android|iPhone|iPod|Windows Phone|IEMobile/i', $userAgent) => 'mobile',
            default => 'desktop',
        };
    }

    private static function browserName(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        // Order matters: most browsers impersonate Chrome/Safari in their UA,
        // so the more specific brands have to be matched first.
        foreach ([
            'OPR|Opera' => 'Opera',
            'Edg' => 'Edge',
            'SamsungBrowser' => 'Samsung Internet',
            'UCBrowser' => 'UC Browser',
            'YaBrowser' => 'Yandex',
            'Firefox|FxiOS' => 'Firefox',
            'Chrome|CriOS' => 'Chrome',
            'Safari' => 'Safari',
        ] as $pattern => $name) {
            if (preg_match("/{$pattern}/i", $userAgent)) {
                return $name;
            }
        }

        return null;
    }

    private static function platformName(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        foreach ([
            'Windows' => 'Windows',
            'iPhone|iPad|iPod' => 'iOS',
            'Mac OS X|Macintosh' => 'macOS',
            'Android' => 'Android',
            'CrOS' => 'ChromeOS',
            'Linux' => 'Linux',
        ] as $pattern => $name) {
            if (preg_match("/{$pattern}/i", $userAgent)) {
                return $name;
            }
        }

        return null;
    }
}
