<?php

namespace App\Http\Middleware;

use App\Models\ChatConversation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stateless guest authentication for the public chat widget.
 *
 * The public site runs without sessions so its HTML stays cacheable, so the
 * widget instead holds a random per-conversation token in localStorage and
 * sends it as a bearer header. Because the credential lives in JavaScript-only
 * storage rather than a cookie, the browser never attaches it to cross-site
 * requests and CSRF is structurally impossible here.
 */
class ResolveChatGuest
{
    public const ATTRIBUTE = 'chat.conversation';

    public const HEADER = 'X-Chat-Token';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->hasAcceptableOrigin($request)) {
            return response()->json(['message' => 'Permintaan ditolak.'], 403);
        }

        $conversation = $this->resolveConversation($request);

        if ($conversation?->is_blocked) {
            return response()->json([
                'message' => 'Percakapan ini telah ditutup oleh admin.',
            ], 403);
        }

        $request->attributes->set(self::ATTRIBUTE, $conversation);

        $response = $next($request);

        // Chat payloads are per-visitor and must never be held by the CDN or
        // the browser cache that the rest of the public site relies on.
        $response->headers->set('Cache-Control', 'no-store, private, max-age=0');

        return $response;
    }

    private function resolveConversation(Request $request): ?ChatConversation
    {
        $token = trim((string) $request->header(self::HEADER, ''));

        // Reject malformed tokens before touching the database: the column is
        // a fixed-length hex string, so anything else cannot match a row.
        if (strlen($token) !== 64 || ! ctype_xdigit($token)) {
            return null;
        }

        return ChatConversation::query()
            ->where('public_token', $token)
            ->first();
    }

    /**
     * Defence in depth against scripted abuse from other origins. Browsers
     * always send Origin on cross-origin requests and on same-origin POSTs, so
     * a present-but-foreign Origin is a reliable reject signal. A missing
     * Origin is allowed because the token check above is the real gate.
     */
    private function hasAcceptableOrigin(Request $request): bool
    {
        $origin = $request->headers->get('Origin');

        if ($origin === null || $origin === '') {
            return true;
        }

        return parse_url($origin, PHP_URL_HOST) === $request->getHost();
    }
}
