<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

abstract class TestCase extends BaseTestCase
{
    private static bool $baselineChecked = false;

    /** Bootstrap tests only against a dedicated MySQL test database. */
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $connection = (string) $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'mysql' || ! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                'PHPUnit wajib menggunakan koneksi MySQL ke database khusus berakhiran _test.'
            );
        }

        $this->assertBaselineSeeded($database);

        return $app;
    }

    /**
     * Tests memakai DatabaseTransactions, bukan RefreshDatabase, sehingga baris
     * baseline dari DatabaseSeeder harus sudah ada sebelum suite berjalan.
     * Tanpa pemeriksaan ini, database test yang ter-migrate tapi belum di-seed
     * membuat sejumlah test gagal dengan pesan yang menyesatkan (mis. assertion
     * HTML) alih-alih menunjuk pada penyebab sebenarnya.
     */
    private function assertBaselineSeeded(string $database): void
    {
        if (self::$baselineChecked) {
            return;
        }

        self::$baselineChecked = true;

        try {
            $seeded = DB::table('site_settings')->exists() && DB::table('fpk_profiles')->exists();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "Database test '{$database}' belum siap: ".$exception->getMessage()
                ."\nJalankan: DB_DATABASE={$database} php artisan migrate:fresh --seed",
                previous: $exception,
            );
        }

        if (! $seeded) {
            throw new RuntimeException(
                "Database test '{$database}' sudah ter-migrate tetapi belum di-seed.\n"
                ."Jalankan: DB_DATABASE={$database} php artisan migrate:fresh --seed"
            );
        }
    }
}
