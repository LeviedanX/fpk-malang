<?php

namespace Tests\Feature\Admin;

use App\Models\AdminActivityLog;
use App\Models\AdminPinSecurityState;
use App\Models\User;
use App\Support\AdminPinGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSecuritySettingsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_settings_are_hidden_behind_pin_gate(): void
    {
        $user = $this->adminWithPin();

        $this->actingAs($user)
            ->get(route('admin.account.edit'))
            ->assertOk()
            ->assertSee('Masukkan PIN Admin')
            ->assertDontSee('Profil Administrator');
    }

    public function test_admin_can_create_initial_pin_using_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.account.pin.setup'), [
                'current_password' => 'password',
                'pin' => '483920',
                'pin_confirmation' => '483920',
            ])
            ->assertRedirect(route('admin.account.edit'));

        $this->assertTrue(Hash::check('483920', $user->fresh()->admin_pin));
    }

    public function test_correct_pin_opens_settings_for_one_entry(): void
    {
        $user = $this->adminWithPin();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Trusted PIN Browser')
            ->post(route('admin.account.unlock'), ['pin' => '483920'])
            ->assertRedirect(route('admin.account.edit'));

        $this->withHeader('User-Agent', 'Trusted PIN Browser')
            ->get(route('admin.account.edit'))
            ->assertOk()
            ->assertSee('Profil Administrator')
            ->assertSee('Histori Aktivitas Admin');

        $this->withHeader('User-Agent', 'Trusted PIN Browser')
            ->get(route('admin.account.edit'))
            ->assertOk()
            ->assertSee('Masukkan PIN Admin')
            ->assertDontSee('Profil Administrator');
    }

    public function test_pin_lockout_escalates_after_each_three_failures_and_caps_at_one_hour(): void
    {
        Carbon::setTestNow('2026-07-27 10:00:00');
        $user = $this->adminWithPin();
        $client = $this->actingAs($user)->withHeader('User-Agent', 'Escalating PIN Test Browser');

        foreach ([30, 60, 300, 900, 1800, 3600, 3600] as $expectedDelay) {
            foreach (range(1, 3) as $ignored) {
                $client->post(route('admin.account.unlock'), ['pin' => '000000'])
                    ->assertRedirect(route('admin.account.edit'));
            }

            $state = AdminPinSecurityState::query()->where('user_id', $user->id)->firstOrFail();
            $this->assertSame($expectedDelay, (int) now()->diffInSeconds($state->locked_until));

            $this->travel($expectedDelay + 1)->seconds();
        }
    }

    public function test_sensitive_account_mutations_cannot_bypass_pin_gate(): void
    {
        $user = $this->adminWithPin();

        $this->actingAs($user)
            ->put(route('admin.account.update'), [
                'name' => 'Nama Tidak Sah',
                'email' => 'bypass@example.test',
                'current_password' => 'password',
            ])
            ->assertRedirect(route('admin.account.edit'))
            ->assertSessionHasErrors('pin');

        $this->assertNotSame('bypass@example.test', $user->fresh()->email);
    }

    public function test_admin_can_change_pin_after_unlocking_settings(): void
    {
        $user = $this->adminWithPin();

        $this->actingAs($user)
            ->withSession($this->unlockedSession())
            ->put(route('admin.account.pin.update'), [
                'current_pin' => '483920',
                'pin' => '720461',
                'pin_confirmation' => '720461',
            ])
            ->assertRedirect(route('admin.account.edit'));

        $this->assertTrue(Hash::check('720461', $user->fresh()->admin_pin));
        $this->assertFalse(Hash::check('483920', $user->fresh()->admin_pin));
    }

    public function test_admin_requests_are_recorded_and_history_can_be_cleared(): void
    {
        $user = $this->adminWithPin();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk();

        $this->assertDatabaseHas('admin_activity_logs', [
            'user_id' => $user->id,
            'event' => 'admin.dashboard',
            'status_code' => 200,
        ]);

        foreach (range(1, 2) as $index) {
            AdminActivityLog::create([
                'user_id' => $user->id,
                'event' => 'test.activity.'.$index,
                'description' => 'Aktivitas pengujian '.$index,
                'method' => 'GET',
                'path' => '/admin/test-'.$index,
                'status_code' => 200,
            ]);
        }

        $this->actingAs($user)
            ->withSession($this->unlockedSession())
            ->delete(route('admin.account.logs.clear'))
            ->assertRedirect(route('admin.account.edit'));

        $this->assertSame(1, AdminActivityLog::query()->count());
        $this->assertDatabaseHas('admin_activity_logs', [
            'event' => 'admin.account.logs.clear',
        ]);
    }

    /** @return array<string, int> */
    private function unlockedSession(): array
    {
        return [
            AdminPinGate::ACTION_UNTIL_KEY => now()->addMinutes(5)->getTimestamp(),
        ];
    }

    private function adminWithPin(): User
    {
        return User::factory()->create([
            'admin_pin' => Hash::make('483920'),
        ]);
    }
}
