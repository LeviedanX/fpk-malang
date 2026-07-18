<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MySqlIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_application_infrastructure_uses_mysql(): void
    {
        $this->assertSame('mysql', config('database.default'));
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame('fpk_malang', DB::connection()->getDatabaseName());

        $this->assertSame('database', config('cache.default'));
        $this->assertSame('database', config('session.driver'));
        $this->assertSame('database', config('queue.default'));

        $this->assertTrue(Schema::hasTable('cache'));
        $this->assertTrue(Schema::hasTable('sessions'));
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertInstanceOf(DatabaseQueue::class, Queue::connection());

        Cache::put('mysql-integration-check', 'connected', 60);
        $this->assertSame('connected', Cache::get('mysql-integration-check'));
        Cache::forget('mysql-integration-check');
    }
}
