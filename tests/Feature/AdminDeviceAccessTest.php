<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Panel admin dibatasi hanya untuk perangkat desktop.
 *
 * Ini gerbang kebijakan, bukan batas keamanan: user agent bisa dipalsukan.
 * Tujuannya memastikan panel hanya dioperasikan pada layar yang memang
 * dirancang untuknya, dan ponsel tidak pernah sampai ke formulir password.
 */
class AdminDeviceAccessTest extends TestCase
{
    use DatabaseTransactions;

    private const DESKTOP_USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/136.0 Safari/537.36';

    private const ANDROID_PHONE = 'Mozilla/5.0 (Linux; Android 15) AppleWebKit/537.36 Mobile Safari/537.36';

    private const IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1';

    /**
     * @return list<string>
     */
    private function blockedUserAgents(): array
    {
        return [
            'Mozilla/5.0 (Linux; Android 15; Pixel 9 Pro) AppleWebKit/537.36 Chrome/136.0 Mobile Safari/537.36',
            self::IPHONE,
            // iPad dalam mode seluler.
            'Mozilla/5.0 (iPad; CPU OS 18_5 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
            // Tablet Android — tidak memuat kata "Mobile".
            'Mozilla/5.0 (Linux; Android 14; SM-X200) AppleWebKit/537.36 Chrome/136.0 Safari/537.36',
        ];
    }

    public function test_desktop_can_reach_admin_login(): void
    {
        $this->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get('/admin/login')
            ->assertOk()
            ->assertSee('Masuk');
    }

    public function test_desktop_admin_can_reach_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_mobile_and_tablet_are_blocked_from_admin_login(): void
    {
        foreach ($this->blockedUserAgents() as $userAgent) {
            $this->withHeader('User-Agent', $userAgent)
                ->get('/admin/login')
                ->assertForbidden()
                ->assertSee('Panel admin hanya untuk desktop');
        }
    }

    public function test_mobile_cannot_submit_the_login_form(): void
    {
        $user = User::factory()->create();

        $this->withHeader('User-Agent', self::ANDROID_PHONE)
            ->post('/admin/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertForbidden();

        $this->assertGuest();
    }

    /**
     * Sesi yang dibuat di desktop tidak boleh jadi jalan pintas: begitu cookie
     * yang sama dipakai dari ponsel, panel tetap tertutup.
     */
    public function test_authenticated_admin_is_still_blocked_on_mobile(): void
    {
        $this->actingAs(User::factory()->create())
            ->withHeader('User-Agent', self::IPHONE)
            ->get('/admin')
            ->assertForbidden()
            ->assertDontSee('Ruang Kerja Admin');
    }

    public function test_every_admin_section_is_blocked_on_mobile(): void
    {
        $user = User::factory()->create();

        $paths = [
            '/admin',
            '/admin/artikel',
            '/admin/agenda',
            '/admin/galeri',
            '/admin/chat',
            '/admin/pengurus/periode',
            '/admin/pengaturan',
            '/admin/pengaturan-admin',
        ];

        foreach ($paths as $path) {
            $this->actingAs($user)
                ->withHeader('User-Agent', self::ANDROID_PHONE)
                ->get($path)
                ->assertForbidden();
        }
    }

    /**
     * Chromium melaporkan tablet sebagai Sec-CH-UA-Mobile: ?0, jadi hint tidak
     * boleh dipakai sendirian untuk meloloskan perangkat.
     */
    public function test_mobile_client_hint_blocks_even_with_a_desktop_user_agent(): void
    {
        $this->withHeaders([
            'User-Agent' => self::DESKTOP_USER_AGENT,
            'Sec-CH-UA-Mobile' => '?1',
        ])->get('/admin/login')
            ->assertForbidden();
    }

    public function test_desktop_client_hint_does_not_override_a_mobile_user_agent(): void
    {
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; SM-X200) AppleWebKit/537.36 Chrome/136.0 Safari/537.36',
            'Sec-CH-UA-Mobile' => '?0',
        ])->get('/admin/login')
            ->assertForbidden();
    }

    public function test_public_site_stays_open_to_mobile_visitors(): void
    {
        $this->withHeader('User-Agent', self::IPHONE)
            ->get('/')
            ->assertOk();
    }

    /**
     * iPad mode desktop mengirim user agent Macintosh tanpa client hint,
     * sehingga hanya bisa dihentikan di browser.
     */
    public function test_admin_layouts_carry_the_ipad_desktop_mode_guard(): void
    {
        $this->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get('/admin/login')
            ->assertOk()
            ->assertSee('navigator.maxTouchPoints', escape: false);

        $this->actingAs(User::factory()->create())
            ->withHeader('User-Agent', self::DESKTOP_USER_AGENT)
            ->get('/admin')
            ->assertOk()
            ->assertSee('navigator.maxTouchPoints', escape: false);
    }
}
