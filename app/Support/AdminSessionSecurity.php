<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSessionSecurity
{
    private const FINGERPRINT_KEY = 'admin_session_fingerprint';

    private const STARTED_AT_KEY = 'admin_session_started_at';

    private const ROTATED_AT_KEY = 'admin_session_rotated_at';

    public function establish(Request $request, User $user): void
    {
        $now = now()->getTimestamp();

        $request->session()->put([
            self::FINGERPRINT_KEY => $this->fingerprint($request),
            self::STARTED_AT_KEY => $now,
            self::ROTATED_AT_KEY => $now,
        ]);

        $this->revokeOtherSessions($user, $request->session()->getId());
    }

    public function isValid(Request $request, User $user): bool
    {
        $storedFingerprint = $request->session()->get(self::FINGERPRINT_KEY);
        $startedAt = $request->session()->get(self::STARTED_AT_KEY);

        // Upgrade an authenticated session created before this hardening was
        // deployed. Fresh logins always establish these values explicitly.
        if (! is_string($storedFingerprint) || ! is_numeric($startedAt)) {
            $this->establish($request, $user);

            return true;
        }

        if (! hash_equals($storedFingerprint, $this->fingerprint($request))) {
            return false;
        }

        $absoluteLifetime = max(
            300,
            (int) config('security.admin_session.absolute_lifetime', 8 * 60 * 60),
        );

        return now()->getTimestamp() - (int) $startedAt <= $absoluteLifetime;
    }

    public function rotateIfDue(Request $request): void
    {
        $lastRotation = (int) $request->session()->get(self::ROTATED_AT_KEY, 0);
        $rotationInterval = max(
            60,
            (int) config('security.admin_session.rotation_interval', 15 * 60),
        );

        if (now()->getTimestamp() - $lastRotation < $rotationInterval) {
            return;
        }

        $request->session()->migrate(true);
        $request->session()->put(self::ROTATED_AT_KEY, now()->getTimestamp());
    }

    public function refreshAfterCredentialChange(Request $request, User $user): void
    {
        $request->session()->migrate(true);
        $this->establish($request, $user);
    }

    private function revokeOtherSessions(User $user, string $currentSessionId): void
    {
        if (! config('security.admin_session.single_login', true)
            || config('session.driver') !== 'database') {
            return;
        }

        $table = (string) config('session.table', 'sessions');

        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where('user_id', $user->getKey())
            ->where('id', '!=', $currentSessionId)
            ->delete();
    }

    private function fingerprint(Request $request): string
    {
        $parts = [
            mb_substr((string) $request->userAgent(), 0, 512),
        ];

        if (config('security.admin_session.bind_ip', false)) {
            $parts[] = (string) $request->ip();
        }

        return hash_hmac(
            'sha256',
            implode('|', $parts),
            (string) config('app.key'),
        );
    }
}
