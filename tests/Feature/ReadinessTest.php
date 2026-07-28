<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReadinessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_readiness_endpoint_checks_database_and_storage_without_creating_session(): void
    {
        $response = $this->get('/ready')
            ->assertOk()
            ->assertJson([
                'status' => 'ready',
                'checks' => [
                    'database' => true,
                    'storage' => true,
                ],
            ])
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->assertFalse($response->headers->has('Set-Cookie'));
    }
}
