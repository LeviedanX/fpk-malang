<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Exceptions\RegisterErrorViewPaths;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class DeploymentConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        TrustProxies::flushState();

        parent::tearDown();
    }

    /**
     * php artisan optimize caches the configuration, and Laravel then skips
     * parsing .env entirely, so env() resolves to null at runtime. bootstrap
     * must not depend on it; that callback also runs for the console kernel
     * before the configuration repository is even bound.
     */
    public function test_bootstrap_never_reads_env_directly(): void
    {
        $bootstrap = (string) file_get_contents(base_path('bootstrap/app.php'));

        $this->assertDoesNotMatchRegularExpression(
            '/(?<![\w>$])env\s*\(/',
            $bootstrap,
            'bootstrap/app.php harus memakai config(), bukan env(); env() bernilai null setelah config di-cache.'
        );
    }

    public function test_trusted_proxies_come_from_cacheable_configuration(): void
    {
        $appConfig = (string) file_get_contents(config_path('app.php'));

        $this->assertStringContainsString("'trusted_proxies' => env('TRUSTED_PROXIES'", $appConfig);
    }

    public function test_boot_registers_the_proxies_listed_in_configuration(): void
    {
        TrustProxies::flushState();
        config(['app.trusted_proxies' => '10.0.0.1, 10.0.0.2 , ']);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame(['10.0.0.1', '10.0.0.2'], $this->registeredProxies());
    }

    public function test_boot_supports_trusting_any_proxy(): void
    {
        TrustProxies::flushState();
        config(['app.trusted_proxies' => '*']);

        (new AppServiceProvider($this->app))->boot();

        $this->assertSame('*', $this->registeredProxies());
    }

    public function test_boot_leaves_proxies_untrusted_when_configuration_is_empty(): void
    {
        TrustProxies::flushState();
        config(['app.trusted_proxies' => '']);

        (new AppServiceProvider($this->app))->boot();

        $this->assertNull($this->registeredProxies());
    }

    /**
     * Without this, every visitor shares the proxy's IP: the login rate limiter
     * collapses into one bucket, the admin activity log records the proxy, and
     * the request never looks secure so HSTS is never emitted.
     */
    public function test_requests_behind_a_trusted_proxy_expose_the_real_client_ip_and_scheme(): void
    {
        TrustProxies::flushState();
        TrustProxies::at('10.0.0.1');

        $request = Request::create('http://fpk.test/', 'GET', server: ['REMOTE_ADDR' => '10.0.0.1']);
        $request->headers->set('X-Forwarded-For', '203.0.113.7');
        $request->headers->set('X-Forwarded-Proto', 'https');

        $seen = null;
        (new TrustProxies)->handle($request, function (Request $forwarded) use (&$seen): Response {
            $seen = $forwarded;

            return new Response;
        });

        $this->assertSame('203.0.113.7', $seen->ip());
        $this->assertTrue($seen->isSecure());
    }

    public function test_forwarded_headers_are_ignored_from_untrusted_sources(): void
    {
        TrustProxies::flushState();
        TrustProxies::at('10.0.0.1');

        $request = Request::create('http://fpk.test/', 'GET', server: ['REMOTE_ADDR' => '198.51.100.9']);
        $request->headers->set('X-Forwarded-For', '203.0.113.7');
        $request->headers->set('X-Forwarded-Proto', 'https');

        $seen = null;
        (new TrustProxies)->handle($request, function (Request $forwarded) use (&$seen): Response {
            $seen = $forwarded;

            return new Response;
        });

        $this->assertSame('198.51.100.9', $seen->ip());
        $this->assertFalse($seen->isSecure());
    }

    /**
     * layouts.public is populated by database-backed view composers, so the
     * degraded pages must not extend it: the failure they report can be the
     * database itself being unreachable.
     */
    public function test_degraded_error_pages_render_without_the_database_backed_layout(): void
    {
        foreach (['500', '503'] as $status) {
            $path = resource_path("views/errors/{$status}.blade.php");

            $this->assertFileExists($path);

            $view = (string) file_get_contents($path);
            $this->assertStringNotContainsString('@extends', $view);
            $this->assertStringContainsString('<!DOCTYPE html>', $view);
        }
    }

    public function test_maintenance_page_resolves_and_renders_the_way_production_does(): void
    {
        // Mirrors how the exception renderer resolves the errors:: namespace.
        (new RegisterErrorViewPaths)();

        $rendered = view('errors::503')->render();

        $this->assertStringContainsString('503', $rendered);
        $this->assertStringContainsString('pemeliharaan', $rendered);
        $this->assertStringContainsString('noindex', $rendered);
    }

    /**
     * @return array<int, string>|string|null
     */
    private function registeredProxies(): array|string|null
    {
        $property = new ReflectionProperty(TrustProxies::class, 'alwaysTrustProxies');

        return $property->getValue();
    }
}
