<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MySqlIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_application_infrastructure_uses_mysql(): void
    {
        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('fpk_malang_test', DB::connection()->getDatabaseName());

        $this->assertSame('array', config('cache.default'));
        $this->assertSame('database', config('session.driver'));
        $this->assertSame('sync', config('queue.default'));

        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasTable('jobs'));
    }
}
