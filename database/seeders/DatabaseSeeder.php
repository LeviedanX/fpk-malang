<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's baseline data.
     *
     * Only safe, production-appropriate defaults are seeded here. Sample articles
     * and agendas live in DemoContentSeeder and must be run separately for local
     * development only.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            FpkProfileSeeder::class,
            ContactSettingSeeder::class,
            ManagementSeeder::class,
        ]);
    }
}
