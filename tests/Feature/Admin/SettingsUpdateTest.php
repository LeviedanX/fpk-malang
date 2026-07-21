<?php

namespace Tests\Feature\Admin;

use App\Models\ContactSetting;
use App\Models\FpkProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SettingsUpdateTest extends TestCase
{
    use DatabaseTransactions;

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
            'hero_title' => $profile->hero_title ?: 'Forum Pembauran Kebangsaan Kota Malang',
            'hero_subtitle' => $profile->hero_subtitle,
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
            'hero_title' => 'Hero Baru FPK',
            'hero_subtitle' => 'Subtitle baru yang dikelola admin.',
            'definition' => 'Definisi terpadu.',
            'email' => 'info@fpkmalang.test',
            'whatsapp' => '6281234567890',
            'default_meta_keywords' => 'FPK, Kota Malang',
        ]))->assertRedirect(route('admin.settings.edit').'#kontak');

        $this->assertDatabaseCount('site_settings', 1);
        $this->assertSame('Situs FPK', SiteSetting::query()->first()->site_name);
        $this->assertSame('Hero Baru FPK', FpkProfile::query()->first()->hero_title);
        $this->assertSame('info@fpkmalang.test', ContactSetting::query()->first()->email);
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

    public function test_required_text_limits_are_enforced_without_saving_partial_data(): void
    {
        $user = User::factory()->create();
        $originalSiteName = SiteSetting::query()->first()->site_name;

        foreach ([
            'site_name' => 60,
            'organization_name' => 100,
            'abbreviation' => 20,
            'tagline' => 120,
            'hero_title' => 100,
            'hero_subtitle' => 180,
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

    public function test_admin_can_change_password_with_correct_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('admin.account.password'), [
            'current_password' => 'password',
            'password' => 'new-strong-password',
            'password_confirmation' => 'new-strong-password',
        ])->assertRedirect(route('admin.account.edit'));

        $this->assertTrue(password_verify('new-strong-password', $user->fresh()->password));
    }
}
