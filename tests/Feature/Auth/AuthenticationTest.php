<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Masuk')
            ->assertSee('data-admin-login-background=', false)
            ->assertSee('class="auth-input', false);
    }

    public function test_admin_can_authenticate(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-password')]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_admin_area(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/logout')->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_registration_route_does_not_exist(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_login_uses_account_scoped_progressive_throttling_without_locking_shared_ip_early(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $client = $this
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.77'])
            ->withHeader('User-Agent', 'Dedicated Lockout Test Device');

        foreach (range(1, 3) as $attempt) {
            $client->post('/admin/login', [
                'email' => 'rotated-attacker-'.$attempt.'@example.test',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $client->post('/admin/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_blocks_account_and_ip_pair_after_five_failures_for_fifteen_minutes(): void
    {
        $user = User::factory()->create(['password' => Hash::make('correct-password')]);
        $client = $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.99']);

        foreach (range(1, 5) as $attempt) {
            $client->post('/admin/login', [
                'email' => $user->email,
                'password' => 'wrong-password-'.$attempt,
            ])->assertSessionHasErrors('email');
        }

        $client->post('/admin/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertSessionHasErrors('email');

        $this->travel(901)->seconds();

        $client->post('/admin/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));
    }
}
