<?php

namespace Tests\Feature\Admin;

use App\Models\ContactSetting;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_site_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('admin.settings.update'), [
            'site_name' => 'Situs FPK',
            'organization_name' => 'Forum Pembauran Kebangsaan Kota Malang',
            'abbreviation' => 'FPK',
            'tagline' => 'Persatuan',
        ])->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseCount('site_settings', 1);
        $this->assertSame('Situs FPK', SiteSetting::query()->first()->site_name);
    }

    public function test_admin_can_update_contact_and_invalid_email_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('admin.contact.update'), [
            'email' => 'not-an-email',
        ])->assertSessionHasErrors('email');

        $this->actingAs($user)->put(route('admin.contact.update'), [
            'email' => 'info@fpkmalang.test',
            'whatsapp' => '6281234567890',
        ])->assertRedirect(route('admin.contact.edit'));

        $this->assertSame('info@fpkmalang.test', ContactSetting::query()->first()->email);
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
