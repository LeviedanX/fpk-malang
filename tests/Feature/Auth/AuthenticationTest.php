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
        $this->get('/admin/login')->assertOk()->assertSee('Masuk');
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

    public function test_login_is_rate_limited_after_five_attempts(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $ignored) {
            $this->post('/admin/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('coba lagi', collect(session('errors')->get('email'))->implode(' '));
    }
}
