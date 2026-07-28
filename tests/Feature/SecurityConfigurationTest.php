<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityConfigurationTest extends TestCase
{
    /**
     * There is no longer a hand-maintained .env.production.example checked
     * into the repo — that was the file that kept drifting out of sync with
     * the real .env. scripts/build-release.ps1 now derives a secret-free,
     * production-forced template from the working .env on every release
     * build, and scripts/audit-release.ps1 asserts these exact guarantees
     * (APP_ENV=production, APP_DEBUG=false, secure/encrypted session, blank
     * APP_KEY/DB_PASSWORD/ADMIN_PASSWORD) against that generated artifact
     * before a release is considered shippable. See README.md § "Artefak
     * handoff (paket rilis)".
     */
    public function test_public_upload_directory_blocks_executable_php_extensions(): void
    {
        $rules = file_get_contents(storage_path('app/public/.htaccess'));

        $this->assertIsString($rules);
        $this->assertStringContainsString('Options -ExecCGI', $rules);
        $this->assertStringContainsString('Require all denied', $rules);
        $this->assertStringContainsString('phtml', $rules);
        $this->assertStringContainsString('phar', $rules);
    }

    public function test_session_serialization_remains_json(): void
    {
        $this->assertSame('json', config('session.serialization'));
    }
}
