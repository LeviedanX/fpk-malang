<?php

namespace Tests\Feature\Admin;

use App\Models\ContactSetting;
use App\Models\FpkProfile;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\AdminPinGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsUpdateTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * A real tiny PNG, so upload coverage does not require the GD extension.
     */
    private function pngUpload(string $name): UploadedFile
    {
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk'
            .'+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        $path = tempnam(sys_get_temp_dir(), 'hero-png');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        $site = SiteSetting::query()->first() ?? SiteSetting::resolveCurrent();
        $profile = FpkProfile::query()->first() ?? FpkProfile::current();

        return array_merge([
            'settings_section' => 'identitas',
            'site_name' => $site->site_name ?: 'FPK Kota Malang',
            'organization_name' => $site->organization_name ?: 'Forum Pembauran Kebangsaan Kota Malang',
            'abbreviation' => $site->abbreviation,
            'tagline' => $site->tagline,
            'footer_text' => $site->footer_text,
            'background_music_visible' => (int) ($site->background_music_visible ?? true),
            'background_music_default_playing' => (int) ($site->background_music_default_playing ?? true),
            'background_music_volume' => $site->background_music_volume ?? 50,
            'hero_eyebrow' => $profile->hero_eyebrow ?: 'Forum Pembauran Kebangsaan Kota Malang',
            'hero_title' => $profile->hero_title ?: 'Forum Pembauran Kebangsaan Kota Malang',
            'hero_subtitle' => $profile->hero_subtitle,
            'hero_primary_cta_label' => $profile->hero_primary_cta_label ?: 'Tentang FPK',
            'hero_secondary_cta_label' => $profile->hero_secondary_cta_label ?: 'Lihat Agenda',
            'hero_legal_basis_label' => $profile->hero_legal_basis_label ?: 'Dasar Hukum',
            'hero_foundation_label' => $profile->hero_foundation_label ?: 'Landasan',
            'hero_period_label' => $profile->hero_period_label ?: 'Masa Bakti',
        ], $overrides);
    }

    public function test_admin_can_update_all_website_settings_from_one_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('admin.settings.update'), $this->validPayload([
            'settings_section' => 'kontak',
            'site_name' => 'Situs FPK',
            'organization_name' => 'Forum Pembauran Kebangsaan Kota Malang',
            'abbreviation' => 'FPK',
            'tagline' => 'Persatuan',
            'hero_eyebrow' => 'Pembauran untuk Semua',
            'hero_title' => 'Hero Baru FPK',
            'hero_subtitle' => 'Subtitle baru yang dikelola admin.',
            'hero_primary_cta_label' => 'Kenali FPK',
            'hero_secondary_cta_label' => 'Agenda Kami',
            'hero_legal_basis_label' => 'Payung Hukum',
            'hero_foundation_label' => 'Dasar Lembaga',
            'hero_period_label' => 'Periode Aktif',
            'definition' => 'Definisi terpadu.',
            'email' => 'info@fpkmalang.test',
            'whatsapp' => '6281234567890',
            'default_meta_keywords' => 'FPK, Kota Malang',
        ]))->assertRedirect(route('admin.settings.edit').'#kontak');

        $this->assertDatabaseCount('site_settings', 1);
        $this->assertSame('Situs FPK', SiteSetting::query()->first()->site_name);
        $profile = FpkProfile::query()->first();
        $this->assertSame('Pembauran untuk Semua', $profile->hero_eyebrow);
        $this->assertSame('Hero Baru FPK', $profile->hero_title);
        $this->assertSame('Kenali FPK', $profile->hero_primary_cta_label);
        $this->assertSame('Payung Hukum', $profile->hero_legal_basis_label);
        $this->assertSame('info@fpkmalang.test', ContactSetting::query()->first()->email);
    }

    public function test_admin_can_manage_separate_desktop_and_mobile_hero_backgrounds(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'beranda',
                'hero_background' => $this->pngUpload('hero-desktop-awal.png'),
                'hero_mobile_background' => $this->pngUpload('hero-mobile-awal.png'),
            ]))
            ->assertRedirect(route('admin.settings.edit').'#beranda')
            ->assertSessionDoesntHaveErrors();

        $profile = FpkProfile::query()->first();
        $firstDesktopPath = $profile->hero_background_path;
        $mobilePath = $profile->hero_mobile_background_path;

        $this->assertNotNull($firstDesktopPath);
        $this->assertNotNull($mobilePath);
        Storage::disk('public')->assertExists($firstDesktopPath);
        Storage::disk('public')->assertExists($mobilePath);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-hero-background="desktop"', false)
            ->assertSee('data-hero-background="mobile"', false)
            ->assertSee('data-hero-background-source="mobile"', false)
            ->assertSee(Storage::url($firstDesktopPath), false)
            ->assertSee(Storage::url($mobilePath), false);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'beranda',
                'hero_background' => $this->pngUpload('hero-desktop-baru.png'),
            ]))
            ->assertRedirect(route('admin.settings.edit').'#beranda')
            ->assertSessionDoesntHaveErrors();

        $profile->refresh();
        $secondDesktopPath = $profile->hero_background_path;

        $this->assertNotSame($firstDesktopPath, $secondDesktopPath);
        $this->assertSame($mobilePath, $profile->hero_mobile_background_path);
        Storage::disk('public')->assertMissing($firstDesktopPath);
        Storage::disk('public')->assertExists($secondDesktopPath);
        Storage::disk('public')->assertExists($mobilePath);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'beranda',
                'remove_hero_background' => '1',
                'remove_hero_mobile_background' => '1',
            ]))
            ->assertRedirect(route('admin.settings.edit').'#beranda')
            ->assertSessionDoesntHaveErrors();

        $profile->refresh();
        $this->assertNull($profile->hero_background_path);
        $this->assertNull($profile->hero_mobile_background_path);
        Storage::disk('public')->assertMissing($secondDesktopPath);
        Storage::disk('public')->assertMissing($mobilePath);
    }

    public function test_admin_can_upload_and_remove_login_background(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'identitas',
                'admin_login_background' => $this->pngUpload('login-background.png'),
            ]))
            ->assertRedirect(route('admin.settings.edit').'#identitas')
            ->assertSessionDoesntHaveErrors();

        $settings = SiteSetting::query()->first();
        $backgroundPath = $settings->admin_login_background_path;

        $this->assertNotNull($backgroundPath);
        Storage::disk('public')->assertExists($backgroundPath);

        auth()->logout();
        app()->forgetInstance('fpk.site_setting');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-admin-login-background="custom"', false)
            ->assertSee(Storage::url($backgroundPath), false)
            ->assertSee('class="auth-input', false);

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'identitas',
                'remove_admin_login_background' => '1',
            ]))
            ->assertRedirect(route('admin.settings.edit').'#identitas')
            ->assertSessionDoesntHaveErrors();

        $this->assertNull($settings->fresh()->admin_login_background_path);
        Storage::disk('public')->assertMissing($backgroundPath);

        auth()->logout();
        app()->forgetInstance('fpk.site_setting');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('data-admin-login-background="default"', false);
    }

    public function test_admin_can_save_background_music_controls(): void
    {
        $user = User::factory()->create();
        $originalVersion = SiteSetting::query()->first()->background_music_preference_version;

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'background_music_visible' => '0',
                'background_music_default_playing' => '0',
                'background_music_volume' => '37',
            ]))
            ->assertRedirect(route('admin.settings.edit').'#identitas')
            ->assertSessionDoesntHaveErrors();

        $settings = SiteSetting::query()->first();

        $this->assertFalse($settings->background_music_visible);
        $this->assertFalse($settings->background_music_default_playing);
        $this->assertSame(37, $settings->background_music_volume);
        $this->assertSame($originalVersion + 1, $settings->background_music_preference_version);
    }

    public function test_unrelated_settings_update_does_not_reset_music_preference_version(): void
    {
        $user = User::factory()->create();
        $settings = SiteSetting::query()->first();
        $originalVersion = $settings->background_music_preference_version;

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'tagline' => 'Tagline tanpa perubahan konfigurasi musik',
            ]))
            ->assertRedirect(route('admin.settings.edit').'#identitas')
            ->assertSessionDoesntHaveErrors();

        $this->assertSame(
            $originalVersion,
            $settings->fresh()->background_music_preference_version,
        );
    }

    public function test_background_music_volume_must_be_between_zero_and_one_hundred(): void
    {
        $user = User::factory()->create();
        $originalVolume = SiteSetting::query()->first()->background_music_volume;

        foreach ([-1, 101] as $invalidVolume) {
            $this->actingAs($user)
                ->put(route('admin.settings.update'), $this->validPayload([
                    'background_music_volume' => $invalidVolume,
                ]))
                ->assertSessionHasErrors('background_music_volume');

            $this->assertSame(
                $originalVolume,
                SiteSetting::query()->first()->background_music_volume,
            );
        }
    }

    public function test_mobile_hero_uses_desktop_background_as_fallback(): void
    {
        Storage::fake('public');

        $profile = FpkProfile::query()->first();
        $profile->update([
            'hero_background_path' => 'profile/hero-desktop-fallback.webp',
            'hero_mobile_background_path' => null,
        ]);

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-hero-background-source="desktop-fallback"', false);

        $this->assertSame(
            3,
            substr_count($response->getContent(), Storage::url($profile->hero_background_path)),
        );
    }

    public function test_invalid_contact_data_is_rejected_by_unified_settings_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'kontak',
                'email' => 'not-an-email',
            ]))
            ->assertSessionHasErrors('email');
    }

    public function test_only_official_google_maps_embed_urls_are_accepted(): void
    {
        $user = User::factory()->create();
        $officialUrl = 'https://www.google.com/maps/embed?pb=official-map-data';

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'kontak',
                'map_embed_url' => $officialUrl,
            ]))
            ->assertRedirect(route('admin.settings.edit').'#kontak')
            ->assertSessionDoesntHaveErrors();

        $this->assertSame($officialUrl, ContactSetting::query()->first()->map_embed_url);
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('sandbox="allow-scripts allow-same-origin allow-popups allow-forms"', false)
            ->assertSee('referrerpolicy="strict-origin-when-cross-origin"', false);

        foreach ([
            'http://www.google.com/maps/embed?pb=insecure',
            'https://evil.example/maps/embed?pb=third-party',
            'https://google.com.evil.example/maps/embed?pb=lookalike',
            'https://www.google.com/maps/place/Malang',
            '<iframe src="https://www.google.com/maps/embed?pb=raw-html"></iframe>',
        ] as $invalidUrl) {
            $this->actingAs($user)
                ->put(route('admin.settings.update'), $this->validPayload([
                    'settings_section' => 'kontak',
                    'map_embed_url' => $invalidUrl,
                ]))
                ->assertSessionHasErrors('map_embed_url');

            $this->assertSame($officialUrl, ContactSetting::query()->first()->map_embed_url);
        }
    }

    public function test_social_media_fields_only_accept_official_https_hosts(): void
    {
        $user = User::factory()->create();

        foreach ([
            'instagram_url' => 'https://www.instagram.com/fpkmalang',
            'facebook_url' => 'https://www.facebook.com/fpkmalang',
            'youtube_url' => 'https://www.youtube.com/@fpkmalang',
            'tiktok_url' => 'https://www.tiktok.com/@fpkmalang',
        ] as $field => $officialUrl) {
            $this->actingAs($user)
                ->put(route('admin.settings.update'), $this->validPayload([
                    'settings_section' => 'kontak',
                    $field => $officialUrl,
                ]))
                ->assertSessionDoesntHaveErrors($field);
        }

        foreach ([
            'instagram_url' => 'http://www.instagram.com/fpkmalang',
            'facebook_url' => 'https://facebook.com.evil.example/fpkmalang',
            'youtube_url' => 'https://evil.example/@fpkmalang',
            'tiktok_url' => 'https://user:password@www.tiktok.com/@fpkmalang',
        ] as $field => $invalidUrl) {
            $this->actingAs($user)
                ->put(route('admin.settings.update'), $this->validPayload([
                    'settings_section' => 'kontak',
                    $field => $invalidUrl,
                ]))
                ->assertSessionHasErrors($field);
        }
    }

    public function test_required_text_limits_are_enforced_without_saving_partial_data(): void
    {
        $user = User::factory()->create();
        $originalSiteName = SiteSetting::query()->first()->site_name;

        foreach ([
            'site_name' => 60,
            'organization_name' => 100,
            'abbreviation' => 20,
            'tagline' => 120,
            'hero_eyebrow' => 120,
            'hero_title' => 100,
            'hero_subtitle' => 180,
            'hero_primary_cta_label' => 60,
            'hero_secondary_cta_label' => 60,
            'hero_legal_basis_label' => 60,
            'hero_foundation_label' => 60,
            'hero_period_label' => 60,
            'footer_text' => 180,
        ] as $field => $limit) {
            $this->actingAs($user)
                ->put(route('admin.settings.update'), $this->validPayload([
                    $field => str_repeat('x', $limit + 1),
                ]))
                ->assertSessionHasErrors($field);

            $this->assertSame($originalSiteName, SiteSetting::query()->first()->site_name);
        }
    }

    public function test_default_seo_title_is_limited_to_sixty_characters(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('admin.settings.update'), $this->validPayload([
                'settings_section' => 'seo',
                'default_meta_title' => str_repeat('x', 61),
            ]))
            ->assertSessionHasErrors('default_meta_title');
    }

    public function test_admin_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession([AdminPinGate::ACTION_UNTIL_KEY => now()->addMinutes(5)->getTimestamp()])
            ->put(route('admin.account.password'), [
                'current_password' => 'password',
                'password' => 'New-Strong-Password-2026!',
                'password_confirmation' => 'New-Strong-Password-2026!',
            ])->assertRedirect(route('admin.account.edit'));

        $this->assertTrue(password_verify('New-Strong-Password-2026!', $user->fresh()->password));
    }
}
