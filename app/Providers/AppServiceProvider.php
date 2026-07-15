<?php

namespace App\Providers;

use App\Models\ContactSetting;
use App\Models\SiteSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Request-scoped singletons: one query each per request, reset between
        // requests (and between tests, since each test boots a fresh app).
        $this->app->scoped('fpk.site_setting', fn () => SiteSetting::resolveCurrent());
        $this->app->scoped('fpk.contact_setting', fn () => ContactSetting::resolveCurrent());
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        // Branding needed by the chrome of public, admin, and auth pages.
        View::composer(
            ['layouts.public', 'layouts.admin', 'layouts.auth', 'public-site.*'],
            fn ($view) => $view->with('site', SiteSetting::current())
        );

        // Contact data needed by the public footer and homepage.
        View::composer(
            ['layouts.public', 'public-site.*'],
            fn ($view) => $view->with('contact', ContactSetting::current())
        );
    }
}
