<?php

namespace App\Providers;

use App\Models\Agenda;
use App\Models\Article;
use App\Models\ContactSetting;
use App\Models\ManagementPeriod;
use App\Models\SiteSetting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        config(['database.connections' => [
            'mysql' => config('database.connections.mysql'),
        ]]);

        if (config('database.default') !== 'mysql') {
            throw new RuntimeException('Aplikasi FPK wajib menggunakan koneksi database MySQL.');
        }

        // Request-scoped singletons: one query each per request, reset between
        // requests (and between tests, since each test boots a fresh app).
        $this->app->scoped('fpk.site_setting', fn () => SiteSetting::resolveCurrent());
        $this->app->scoped('fpk.contact_setting', fn () => ContactSetting::resolveCurrent());
        $this->app->scoped('fpk.public_content_visibility', function (): array {
            $contact = ContactSetting::current();

            return [
                'articles' => Article::query()->published()->exists(),
                'agendas' => Agenda::query()->published()->exists(),
                'management' => ManagementPeriod::query()
                    ->active()
                    ->where(fn ($query) => $query
                        ->where('group_photo_path', '!=', '')
                        ->orWhereHas('activeMembers'))
                    ->exists(),
                'contact' => $contact->hasAnyContact() || filled($contact->map_embed_url),
            ];
        });
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

        View::composer(
            [
                'public-site.home',
                'public-site.partials.navbar',
                'public-site.partials.footer',
            ],
            fn ($view) => $view->with(
                'publicContentVisibility',
                $this->app->make('fpk.public_content_visibility'),
            )
        );
    }
}
