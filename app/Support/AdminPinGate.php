<?php

namespace App\Support;

use App\Models\AdminPinSecurityState;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminPinGate
{
    public const ACTION_UNTIL_KEY = 'admin_settings_actions_until';

    private const ENTRY_GRANTED_KEY = 'admin_settings_entry_granted';

    private const DELAYS = [30, 60, 300, 900, 1800, 3600];

    private const MAX_FAILURES = 3;

    public function __construct(private readonly AdminDeviceIdentity $deviceIdentity) {}

    /**
     * @return array{setup_required: bool, locked: bool, seconds: int}
     */
    public function status(Request $request, User $user): array
    {
        $state = $this->state($request, $user);
        $seconds = $state->locked_until?->isFuture()
            ? max(1, now()->diffInSeconds($state->locked_until, false))
            : 0;

        return [
            'setup_required' => ! is_string($user->admin_pin) || $user->admin_pin === '',
            'locked' => $seconds > 0,
            'seconds' => $seconds,
        ];
    }

    /**
     * @return array{valid: bool, locked: bool, seconds: int}
     */
    public function verify(Request $request, User $user, string $pin): array
    {
        return DB::transaction(function () use ($request, $user, $pin): array {
            $state = $this->state($request, $user, true);

            if ($state->locked_until?->isFuture()) {
                return [
                    'valid' => false,
                    'locked' => true,
                    'seconds' => max(1, now()->diffInSeconds($state->locked_until, false)),
                ];
            }

            if (is_string($user->admin_pin)
                && preg_match('/^\d{6}$/', $pin) === 1
                && Hash::check($pin, $user->admin_pin)) {
                $state->update([
                    'failure_count' => 0,
                    'lockout_level' => 0,
                    'locked_until' => null,
                ]);
                $this->grant($request);

                return ['valid' => true, 'locked' => false, 'seconds' => 0];
            }

            $failures = $state->failure_count + 1;

            if ($failures < self::MAX_FAILURES) {
                $state->update([
                    'failure_count' => $failures,
                    'locked_until' => null,
                ]);

                return ['valid' => false, 'locked' => false, 'seconds' => 0];
            }

            $level = min($state->lockout_level + 1, count(self::DELAYS));
            $delay = self::DELAYS[$level - 1];
            $state->update([
                'failure_count' => 0,
                'lockout_level' => $level,
                'locked_until' => now()->addSeconds($delay),
            ]);

            return ['valid' => false, 'locked' => true, 'seconds' => $delay];
        });
    }

    public function grant(Request $request): void
    {
        $request->session()->put([
            self::ENTRY_GRANTED_KEY => true,
            self::ACTION_UNTIL_KEY => now()->addMinutes(15)->getTimestamp(),
        ]);
    }

    public function grantEntry(Request $request): void
    {
        $request->session()->put(self::ENTRY_GRANTED_KEY, true);
    }

    public function consumeEntryGrant(Request $request): bool
    {
        return (bool) $request->session()->pull(self::ENTRY_GRANTED_KEY, false);
    }

    public function canPerformActions(Request $request): bool
    {
        return (int) $request->session()->get(self::ACTION_UNTIL_KEY, 0) >= now()->getTimestamp();
    }

    public function revoke(Request $request): void
    {
        $request->session()->forget([
            self::ENTRY_GRANTED_KEY,
            self::ACTION_UNTIL_KEY,
        ]);
    }

    private function state(Request $request, User $user, bool $lock = false): AdminPinSecurityState
    {
        $query = AdminPinSecurityState::query()
            ->where('user_id', $user->getKey())
            ->where('device_key', $this->deviceIdentity->key($request));

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->firstOrCreate([
            'user_id' => $user->getKey(),
            'device_key' => $this->deviceIdentity->key($request),
        ]);
    }
}
