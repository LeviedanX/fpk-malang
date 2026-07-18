<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Bootstrap tests only against the single approved MySQL database.
     * Feature tests use DatabaseTransactions so their writes are rolled back.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $connection = (string) $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database");

        if ($connection !== 'mysql' || $database !== 'fpk_malang') {
            throw new RuntimeException(
                'PHPUnit wajib menggunakan koneksi MySQL ke database fpk_malang.'
            );
        }

        return $app;
    }
}
