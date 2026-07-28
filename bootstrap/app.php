<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        // Trusted proxies are configured in AppServiceProvider::boot(): this
        // callback also runs for the console kernel before the configuration
        // repository is bound, so config() is not available here yet.

        $middleware->trustHosts(
            at: fn () => [
                '^'.preg_quote((string) parse_url((string) config('app.url'), PHP_URL_HOST), '/').'$',
            ],
            subdomains: false,
        );
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // The chat widget speaks JSON exclusively; without this a validation
        // error or a throttle rejection would render an HTML error page that
        // the fetch() client cannot interpret.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'chat', 'chat/*'),
        );
    })->create();
