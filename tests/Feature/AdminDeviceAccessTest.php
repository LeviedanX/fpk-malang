<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminDeviceAccessTest extends TestCase
{
    use DatabaseTransactions;

    private const DESKTOP_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/136.0 Safari/537.36';

    public function test_desktop_can_access_admin_login(): void
    {
        $this->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get('/admin/login')
            ->assertOk()
            ->assertSee('Masuk');
    }

    public function test_mobile_and_tablet_user_agents_cannot_access_admin_login(): void
    {
        $mobileUserAgents = [
            'Mozilla/5.0 (Linux; Android 15; Pixel 9 Pro) AppleWebKit/537.36 Chrome/136.0 Mobile Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (iPad; CPU OS 18_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
        ];

        foreach ($mobileUserAgents as $userAgent) {
            $this->withHeader('User-Agent', $userAgent)
                ->get('/admin/login')
                ->assertForbidden()
                ->assertSee('Khusus desktop')
                ->assertHeader('Cache-Control', 'no-store, private');
        }
    }

    public function test_mobile_client_hint_cannot_access_admin_login(): void
    {
        $this->withHeaders([
            'User-Agent' => self::DESKTOP_USER_AGENT,
            'Sec-CH-UA-Mobile' => '?1',
        ])->get('/admin/login')
            ->assertForbidden()
            ->assertSee('Khusus desktop');
    }

    public function test_mobile_cannot_submit_admin_login(): void
    {
        $this->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 15) AppleWebKit/537.36 Mobile Safari/537.36')
            ->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'not-submitted',
            ])
            ->assertForbidden();
    }

    public function test_authenticated_mobile_admin_cannot_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('User-Agent', 'Mozilla/5.0 (Linux; Android 15) AppleWebKit/537.36 Mobile Safari/537.36')
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_layout_has_viewport_guard_and_main_site_button_is_removed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get('/admin')
            ->assertOk()
            ->assertSee('data-admin-desktop-content', escape: false)
            ->assertSee('data-admin-desktop-notice', escape: false)
            ->assertDontSee('Lihat situs utama');
    }
}
