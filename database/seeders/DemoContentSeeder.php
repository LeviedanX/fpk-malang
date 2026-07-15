<?php

namespace Database\Seeders;

use App\Models\Agenda;
use App\Models\Article;
use Illuminate\Database\Seeder;

/**
 * DEVELOPMENT ONLY. Generates sample articles and agendas so the public pages can
 * be reviewed locally. This is NOT wired into DatabaseSeeder and must never run in
 * production. Run explicitly: `php artisan db:seed --class=DemoContentSeeder`.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('DemoContentSeeder skipped in production.');

            return;
        }

        Article::factory()->count(7)->create();
        Article::factory()->featured()->create();
        Article::factory()->count(2)->draft()->create();
        Article::factory()->scheduled()->create();

        Agenda::factory()->count(4)->create();
        Agenda::factory()->count(3)->past()->create();
        Agenda::factory()->draft()->create();
    }
}
