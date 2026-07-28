<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $nonce = base64_encode(random_bytes(18));
        $request->attributes->set('csp_nonce', $nonce);

        $response = $next($request);

        foreach ($this->headers($nonce) as $name => $value) {
            $response->headers->set($name, $value);
        }

        if (app()->environment('production')
            && $request->isSecure()
            && Str::startsWith((string) config('app.url'), 'https://')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        if ($request->is('admin', 'admin/*')) {
            $response->headers->set('Cache-Control', 'no-store, private, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $nonce): array
    {
        return [
            'Content-Security-Policy' => $this->contentSecurityPolicy($nonce),
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
        ];
    }

    private function contentSecurityPolicy(string $nonce): string
    {
        $devHttpSources = [];
        $devWebSocketSources = [];
        $localConnectSources = [];
        $localAssetSources = [];

        if (app()->environment('local')) {
            $devHttpSources = [
                'http://localhost:5173',
                'http://127.0.0.1:5173',
                'http://*:5173',
            ];
            $devWebSocketSources = [
                'ws://localhost:5173',
                'ws://127.0.0.1:5173',
                'ws://*:5173',
            ];
            // Opera GX loads its built-in FidelityFX shader through a data URL.
            // Keep this compatibility exception local-only; production remains strict.
            $localConnectSources = ['data:'];
            $localAssetSources = [
                'http://localhost:8000',
                'http://127.0.0.1:8000',
            ];
        }

        $directives = [
            "default-src 'self'",
            $this->directive('script-src', [
                "'self'",
                "'nonce-{$nonce}'",
                // Alpine's expression evaluator requires this until the project
                // migrates to Alpine's CSP-compatible build.
                "'unsafe-eval'",
                ...$devHttpSources,
            ]),
            $this->directive('style-src', ["'self'", "'unsafe-inline'", ...$devHttpSources]),
            $this->directive('img-src', ["'self'", 'data:', 'blob:', ...$localAssetSources]),
            "font-src 'self' data:",
            $this->directive('media-src', ["'self'", 'blob:', ...$localAssetSources]),
            $this->directive('connect-src', [
                "'self'",
                ...$localConnectSources,
                ...$devHttpSources,
                ...$devWebSocketSources,
            ]),
            'frame-src https://www.google.com https://maps.google.com',
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
        ];

        if (app()->environment('production')
            && Str::startsWith((string) config('app.url'), 'https://')) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }

    /**
     * @param  list<string>  $sources
     */
    private function directive(string $name, array $sources): string
    {
        return $name.' '.implode(' ', array_unique($sources));
    }
}
