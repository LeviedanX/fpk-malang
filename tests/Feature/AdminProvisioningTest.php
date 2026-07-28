<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProvisioningTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_seeder_has_no_fallback_credentials(): void
    {
        config([
            'admin.initial.name' => null,
            'admin.initial.email' => null,
            'admin.initial.password' => null,
        ]);
        $before = User::query()->count();

        app(AdminUserSeeder::class)->run();

        $this->assertSame($before, User::query()->count());
        $this->assertDatabaseMissing('users', ['email' => 'admin@fpkmalang.test']);
    }

    public function test_admin_seeder_never_resets_an_existing_password(): void
    {
        $user = User::factory()->create([
            'email' => 'existing-admin@example.test',
            'password' => Hash::make('Original-Password!123'),
        ]);
        config([
            'admin.initial.name' => 'Administrator',
            'admin.initial.email' => $user->email,
            'admin.initial.password' => 'Replacement-Password!456',
        ]);

        app(AdminUserSeeder::class)->run();

        $this->assertTrue(Hash::check('Original-Password!123', $user->fresh()->password));
        $this->assertFalse(Hash::check('Replacement-Password!456', $user->fresh()->password));
    }

    public function test_database_enforces_a_single_site_settings_row(): void
    {
        $this->expectException(QueryException::class);

        SiteSetting::query()->create([
            'singleton_key' => 1,
            'site_name' => 'Duplikat',
            'organization_name' => 'Duplikat',
        ]);
    }
}
