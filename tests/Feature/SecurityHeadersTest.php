<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use DatabaseTransactions;

    public function test_public_responses_include_security_headers_and_matching_script_nonce(): void
    {
        $response = $this->get(route('home'))->assertOk();

        $policy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertNotSame('', $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringContainsString("object-src 'none'", $policy);
        $this->assertStringContainsString("form-action 'self'", $policy);
        $this->assertStringContainsString("connect-src 'self'", $policy);
        $this->assertStringNotContainsString("connect-src 'self' data:", $policy);
        $this->assertMatchesRegularExpression("/script-src [^;]*'nonce-([^']+)'/", $policy);
        preg_match("/script-src [^;]*'nonce-([^']+)'/", $policy, $matches);
        $response->assertSee('nonce="'.$matches[1].'"', false);

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=60', $cacheControl);
        $this->assertStringContainsString('s-maxage=300', $cacheControl);
        $this->assertStringContainsString('stale-while-revalidate=60', $cacheControl);
        $this->assertFalse($response->headers->has('Set-Cookie'));
    }

    public function test_admin_and_login_responses_are_not_cacheable(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-store, private');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, must-revalidate, no-store, private');
    }

    public function test_hsts_is_only_sent_for_secure_production_requests(): void
    {
        $originalEnvironment = app()->environment();
        $originalUrl = config('app.url');

        try {
            app()->detectEnvironment(fn () => 'production');
            config(['app.url' => 'https://fpk.example.test']);

            $request = Request::create('https://fpk.example.test/ready', 'GET');
            $response = (new SecurityHeaders)->handle(
                $request,
                fn (): Response => response('ok'),
            );

            $this->assertSame(
                'max-age=31536000; includeSubDomains',
                $response->headers->get('Strict-Transport-Security'),
            );
            $this->assertStringNotContainsString(
                'data:',
                $this->directive(
                    (string) $response->headers->get('Content-Security-Policy'),
                    'connect-src',
                ),
            );
        } finally {
            app()->detectEnvironment(fn () => $originalEnvironment);
            config(['app.url' => $originalUrl]);
        }
    }

    public function test_local_csp_allows_data_connections_for_opera_gx_shader_compatibility(): void
    {
        $originalEnvironment = app()->environment();

        try {
            app()->detectEnvironment(fn () => 'local');

            $request = Request::create('http://localhost:8000', 'GET');
            $response = (new SecurityHeaders)->handle(
                $request,
                fn (): Response => response('ok'),
            );

            $this->assertStringContainsString(
                'data:',
                $this->directive(
                    (string) $response->headers->get('Content-Security-Policy'),
                    'connect-src',
                ),
            );
        } finally {
            app()->detectEnvironment(fn () => $originalEnvironment);
        }
    }

    private function directive(string $policy, string $name): string
    {
        foreach (explode(';', $policy) as $directive) {
            $directive = trim($directive);

            if (str_starts_with($directive, $name.' ')) {
                return $directive;
            }
        }

        return '';
    }
}
