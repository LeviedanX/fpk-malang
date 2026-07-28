<?php

namespace App\Http\Middleware;

use App\Support\VisitorIdentity;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts the whole admin area — login included — to desktop browsers.
 *
 * This is a policy gate, not a security boundary: a determined user can spoof
 * a desktop user agent. It exists so the panel is only operated on a screen it
 * was designed for, and it deliberately sits in front of the login form so a
 * phone never reaches a password prompt in the first place.
 */
class EnsureDesktopBrowser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isMobileDevice($request)) {
            return $next($request);
        }

        // 403 rather than a redirect: there is no desktop-equivalent URL to
        // send the visitor to, and a redirect loop would be the alternative.
        return response()->view('errors.desktop-only', [], 403);
    }

    private function isMobileDevice(Request $request): bool
    {
        // The client hint can only ever add a block, never clear one: Chromium
        // reports tablets as Sec-CH-UA-Mobile: ?0, so trusting it on its own
        // would wave every Android tablet straight through.
        if (str_contains((string) $request->headers->get('Sec-CH-UA-Mobile'), '?1')) {
            return true;
        }

        return in_array(
            VisitorIdentity::deviceType($request->userAgent()),
            ['mobile', 'tablet'],
            true,
        );
    }
}
