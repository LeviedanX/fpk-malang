<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\AdminPinGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSessionSecurityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_session_is_rejected_when_device_fingerprint_changes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Trusted Admin Browser')
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->withHeader('User-Agent', 'Unknown Hijacker Browser')
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_expired_absolute_admin_session_is_rejected(): void
    {
        $user = User::factory()->create();
        $fingerprint = hash_hmac(
            'sha256',
            'Trusted Admin Browser',
            (string) config('app.key'),
        );

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Trusted Admin Browser')
            ->withSession([
                'admin_session_fingerprint' => $fingerprint,
                'admin_session_started_at' => now()->subHours(9)->getTimestamp(),
                'admin_session_rotated_at' => now()->getTimestamp(),
            ])
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_login_revokes_an_existing_session_for_the_same_admin(): void
    {
        $user = User::factory()->create();
        $this->insertSession('stolen-session-id', $user);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseMissing('sessions', ['id' => 'stolen-session-id']);
    }

    public function test_password_change_revokes_other_sessions(): void
    {
        $user = User::factory()->create();
        $this->insertSession('other-session-id', $user);

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Trusted Admin Browser')
            ->put(route('admin.account.password'), [
                'current_password' => 'password',
                'password' => 'New-Strong-Password-2026!',
                'password_confirmation' => 'New-Strong-Password-2026!',
            ])
            ->assertRedirect(route('admin.account.edit'));

        $this->assertDatabaseMissing('sessions', ['id' => 'other-session-id']);
        $this->assertAuthenticatedAs($user);
    }

    public function test_profile_change_requires_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([AdminPinGate::ACTION_UNTIL_KEY => now()->addMinutes(5)->getTimestamp()])
            ->put(route('admin.account.update'), [
                'name' => 'Nama Baru',
                'email' => 'baru@example.test',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertNotSame('baru@example.test', $user->fresh()->email);
    }

    public function test_weak_new_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([AdminPinGate::ACTION_UNTIL_KEY => now()->addMinutes(5)->getTimestamp()])
            ->put(route('admin.account.password'), [
                'current_password' => 'password',
                'password' => 'weak-password',
                'password_confirmation' => 'weak-password',
            ])
            ->assertSessionHasErrors('password');

        $this->assertTrue(password_verify('password', $user->fresh()->password));
    }

    private function insertSession(string $id, User $user): void
    {
        DB::table('sessions')->insert([
            'id' => $id,
            'user_id' => $user->getKey(),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Old Browser',
            'payload' => base64_encode('old-session'),
            'last_activity' => now()->getTimestamp(),
        ]);
    }
}
