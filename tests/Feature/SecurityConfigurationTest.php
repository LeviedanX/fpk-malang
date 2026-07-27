<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityConfigurationTest extends TestCase
{
    public function test_production_environment_template_has_secure_session_defaults_without_secrets(): void
    {
        $template = file_get_contents(base_path('.env.production.example'));

        $this->assertIsString($template);
        $this->assertStringContainsString('APP_ENV=production', $template);
        $this->assertStringContainsString('APP_DEBUG=false', $template);
        $this->assertStringContainsString('SESSION_ENCRYPT=true', $template);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $template);
        $this->assertStringContainsString('SESSION_HTTP_ONLY=true', $template);
        $this->assertStringContainsString('SESSION_SAME_SITE=strict', $template);
        $this->assertMatchesRegularExpression('/^APP_KEY=\\R/m', $template);
        $this->assertMatchesRegularExpression('/^DB_PASSWORD=\\R/m', $template);
    }

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
