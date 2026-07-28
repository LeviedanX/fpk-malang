<?php

namespace App\Providers;

use App\Models\Agenda;
use App\Models\Article;
use App\Models\ContactSetting;
use App\Models\FpkProfile;
use App\Models\GalleryImage;
use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\SiteSetting;
use App\Support\ChatManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
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
        $this->app->scoped('fpk.profile', fn () => FpkProfile::resolveCurrent());
        $this->app->scoped('fpk.contact_setting', fn () => ContactSetting::resolveCurrent());
        $this->app->scoped('fpk.public_content_visibility', function (): array {
            $hint = request()->attributes->get('fpk.public_content_visibility');

            if (is_array($hint)) {
                return $hint;
            }

            $contact = ContactSetting::current();

            return Cache::remember('fpk.public_content_visibility', 60, fn (): array => [
                'articles' => Article::query()->published()->exists(),
                // `exists()` only needs a boolean; remove listing order clauses
                // inherited from the public scopes so MySQL can stop at the
                // first matching row without sorting.
                'agendas' => Agenda::query()->visibleOnPublic()->reorder()->exists(),
                'gallery' => GalleryImage::query()->visible()->reorder()->exists(),
                'management' => ManagementPeriod::query()
                    ->active()
                    ->where(fn ($query) => $query
                        ->where('group_photo_path', '!=', '')
                        ->orWhereHas('activeMembers'))
                    ->exists(),
                'contact' => $contact->hasAnyContact() || filled($contact->map_embed_url),
            ]);
        });
    }

    public function boot(): void
    {
        $this->trustConfiguredProxies();
        $this->configureChatRateLimits();

        foreach ([Article::class, Agenda::class, GalleryImage::class, ManagementPeriod::class, ManagementMember::class] as $model) {
            $model::saved(fn () => Cache::forget('fpk.public_content_visibility'));
            $model::deleted(fn () => Cache::forget('fpk.public_content_visibility'));
        }

        Paginator::useTailwind();
        Password::defaults(fn () => Password::min(12)
            ->mixedCase()
            ->letters()
            ->numbers()
            ->symbols());

        // Branding needed by the chrome of public, admin, and auth pages.
        View::composer(
            ['layouts.public', 'layouts.admin', 'layouts.auth', 'public-site.*', 'admin.*'],
            fn ($view) => $view->with('site', SiteSetting::current())
        );

        // Contact data needed by the public footer and homepage.
        View::composer(
            ['layouts.public', 'public-site.*'],
            fn ($view) => $view->with('contact', ContactSetting::current())
        );

        // Unread chat badge in the admin sidebar. One indexed SUM per admin
        // page; admin responses are already no-store so nothing is cached.
        View::composer(
            'layouts.admin',
            fn ($view) => $view->with('chatUnreadTotal', ChatManager::adminUnreadTotal())
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

    /**
     * Budgets for the public chat endpoints.
     *
     * They are keyed on the client IP because the widget carries no session.
     * Reads are generous enough that a legitimately open panel is never
     * throttled, while writes are tight enough that one host cannot flood the
     * inbox or the upload disk.
     */
    private function configureChatRateLimits(): void
    {
        RateLimiter::for('chat-poll', fn (Request $request) => Limit::perMinute(90)
            ->by((string) $request->ip()));

        RateLimiter::for('chat-read', fn (Request $request) => Limit::perMinute(30)
            ->by((string) $request->ip()));

        RateLimiter::for('chat-send', fn (Request $request) => [
            Limit::perMinute(15)->by((string) $request->ip()),
            Limit::perDay(300)->by((string) $request->ip()),
        ]);
    }

    /**
     * Trust the proxies listed in configuration.
     *
     * This deliberately reads config() instead of env(): php artisan optimize
     * caches the configuration, and Laravel then skips parsing .env entirely,
     * so env() resolves to null at runtime in production. Left untrusted, a
     * reverse proxy makes every visitor share one client IP, which collapses
     * the login rate limiter, mislabels the admin activity log, and hides
     * HTTPS from the request so HSTS is never emitted.
     */
    private function trustConfiguredProxies(): void
    {
        $proxies = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('app.trusted_proxies')),
        ), fn (string $proxy): bool => $proxy !== ''));

        if ($proxies === []) {
            return;
        }

        TrustProxies::at(in_array('*', $proxies, true) ? '*' : $proxies);
    }
}
