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

    public function test_login_blocks_ip_and_device_for_one_hour_after_three_failures(): void
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

        $response = $client->post('/admin/login', [
            'email' => $user->email,
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('email');
        $message = collect(session('errors')->get('email'))->implode(' ');
        $this->assertSame('Email atau password yang Anda masukkan salah.', $message);
        $this->assertStringNotContainsString('coba lagi', mb_strtolower($message));
        $this->assertGuest();

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.88'])
            ->withHeader('User-Agent', 'Dedicated Lockout Test Device')
            ->post('/admin/login', [
                'email' => $user->email,
                'password' => 'correct-password',
            ])
            ->assertSessionHasErrors('email');

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.77'])
            ->withHeader('User-Agent', 'Different Browser On Blocked IP')
            ->post('/admin/login', [
                'email' => $user->email,
                'password' => 'correct-password',
            ])
            ->assertSessionHasErrors('email');

        $this->travel(3601)->seconds();

        $this->withServerVariables(['REMOTE_ADDR' => '198.51.100.77'])
            ->withHeader('User-Agent', 'Dedicated Lockout Test Device')
            ->post('/admin/login', [
                'email' => $user->email,
                'password' => 'correct-password',
            ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }
}
