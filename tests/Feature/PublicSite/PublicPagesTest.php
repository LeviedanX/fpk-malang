<?php

namespace Tests\Feature\PublicSite;

use App\Models\Agenda;
use App\Models\Article;
use App\Models\ContactSetting;
use App\Models\FpkProfile;
use App\Models\ManagementMember;
use App\Models\ManagementPeriod;
use App\Models\SiteSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Tentang FPK');
    }

    public function test_about_section_uses_high_contrast_redesigned_cards(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('about-feature-card', false)
            ->assertSee('about-card-copy', false)
            ->assertSee('about-card-title', false)
            ->assertDontSee('about-card-icon', false)
            ->assertDontSee('about-card-number', false);

        $this->assertSame(
            4,
            substr_count($response->getContent(), 'about-info-card about-info-card--'),
        );
    }

    public function test_public_cards_use_the_shared_warm_ivory_surface(): void
    {
        Article::factory()->featured()->create();
        Article::factory()->create();
        Agenda::factory()->create();

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('public-facts-card', false);

        $this->assertGreaterThanOrEqual(3, substr_count($response->getContent(), 'surface card-lift'));

        $this->get('/artikel?q=hasil-yang-tidak-ada')
            ->assertOk()
            ->assertSee('public-empty-state', false)
            ->assertSee('surface', false);
    }

    public function test_public_layout_has_no_theme_switcher_or_theme_persistence(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('$store.theme', false)
            ->assertDontSee("localStorage.getItem('theme')", false)
            ->assertDontSee('Aktifkan mode gelap')
            ->assertDontSee('Aktifkan mode terang')
            ->assertDontSee('dark:', false);
    }

    public function test_music_player_uses_admin_default_and_volume_when_visible(): void
    {
        SiteSetting::query()->first()->update([
            'background_music_path' => 'audio/lagu-uji.mp3',
            'background_music_visible' => true,
            'background_music_default_playing' => true,
            'background_music_volume' => 37,
            'background_music_preference_version' => 8,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-site-music-player', false)
            ->assertSee('data-music-default-playing="on"', false)
            ->assertSee('data-music-volume="37"', false)
            ->assertSee('data-music-preference-version="8"', false)
            ->assertSee('data-music-playback-state', false)
            ->assertSee('data-site-music-toggle', false)
            ->assertSee('Putar musik latar', false)
            ->assertSee('Scroll, klik, sentuh, atau tekan tombol untuk memulai musik', false)
            ->assertSee('audio/lagu-uji.mp3', false);
    }

    public function test_admin_can_hide_music_feature_from_public_pages(): void
    {
        SiteSetting::query()->first()->update([
            'background_music_path' => 'audio/lagu-rahasia.mp3',
            'background_music_visible' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('data-site-music-player', false)
            ->assertDontSee('data-site-music-toggle', false)
            ->assertDontSee('audio/lagu-rahasia.mp3', false);
    }

    public function test_admin_login_shortcut_is_desktop_only(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('aria-label="Login Admin"', escape: false);

        $this->assertMatchesRegularExpression(
            '/<a[^>]+href="[^"]*admin\/login"[^>]+aria-label="Login Admin"[^>]+class="[^"]*\bhidden\b[^"]*\blg:inline-flex\b[^"]*"/',
            $response->getContent(),
        );
    }

    public function test_empty_optional_content_is_removed_from_homepage_and_navigation(): void
    {
        $this->removeOptionalPublicContent();

        Article::factory()->draft()->create();
        Agenda::factory()->draft()->create();
        ManagementPeriod::factory()->active()->create();

        $inactivePeriod = ManagementPeriod::factory()->create();
        ManagementMember::factory()->for($inactivePeriod, 'period')->create();

        $this->get('/')
            ->assertOk()
            ->assertDontSee('id="artikel"', escape: false)
            ->assertDontSee('id="agenda"', escape: false)
            ->assertDontSee('id="pengurus"', escape: false)
            ->assertDontSee('id="kontak"', escape: false)
            ->assertDontSee(route('articles.index'), escape: false)
            ->assertDontSee(route('home').'#agenda', escape: false)
            ->assertDontSee(route('home').'#pengurus', escape: false)
            ->assertDontSee(route('home').'#kontak', escape: false)
            ->assertDontSee('Lihat Agenda');
    }

    public function test_optional_content_and_navigation_appear_when_public_data_exists(): void
    {
        $this->removeOptionalPublicContent();

        Article::factory()->create();
        Agenda::factory()->create();

        $period = ManagementPeriod::factory()->active()->create();
        ManagementMember::factory()->for($period, 'period')->create();

        $contact = ContactSetting::query()->first() ?? new ContactSetting;
        $contact->fill(['email' => 'publik@fpk-malang.test'])->save();

        $this->get('/')
            ->assertOk()
            ->assertSee('id="artikel"', escape: false)
            ->assertSee('id="agenda"', escape: false)
            ->assertSee('id="pengurus"', escape: false)
            ->assertSee('id="kontak"', escape: false)
            ->assertSee(route('articles.index'), escape: false)
            ->assertSee(route('home').'#agenda', escape: false)
            ->assertSee(route('home').'#pengurus', escape: false)
            ->assertSee(route('home').'#kontak', escape: false);
    }

    public function test_contact_section_appears_when_only_map_is_available(): void
    {
        $this->removeOptionalPublicContent();

        $contact = ContactSetting::query()->first() ?? new ContactSetting;
        $contact->fill(['map_embed_url' => 'https://maps.google.com/maps?q=Malang&output=embed'])->save();

        $this->get('/')
            ->assertOk()
            ->assertSee('id="kontak"', escape: false)
            ->assertSee('Peta lokasi Forum Pembauran Kebangsaan Kota Malang');
    }

    public function test_home_renders_group_photo_and_member_carousel(): void
    {
        $this->removeOptionalPublicContent();

        $period = ManagementPeriod::factory()->active()->create([
            'group_photo_path' => 'management/foto-bersama.webp',
        ]);

        ManagementMember::factory()->for($period, 'period')->create([
            'name' => 'Ketua Pengurus Uji',
            'position' => 'Ketua',
            'division' => 'Pengurus Inti',
            'portrait_path' => 'management/ketua.webp',
        ]);

        ManagementMember::factory()->for($period, 'period')->create([
            'name' => 'Koordinator Bidang Uji',
            'position' => 'Koordinator',
            'division' => 'Bidang Kerukunan Uji',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Foto bersama pengurus Forum Pembauran Kebangsaan Kota Malang', false)
            ->assertSee('Kenali Pengurus Kami')
            ->assertSee('data-member-carousel', false)
            ->assertSee('data-member-card', false)
            ->assertSee('Kontrol carousel pengurus')
            ->assertSee('Pengurus sebelumnya')
            ->assertSee('Pengurus berikutnya')
            ->assertSee('Ketua Pengurus Uji')
            ->assertSee('Koordinator Bidang Uji')
            ->assertSee('management/ketua.webp', false)
            ->assertDontSee('data-management-directory', false)
            ->assertDontSee('data-management-accordion', false);
    }

    public function test_active_period_is_the_hero_and_management_period_source(): void
    {
        $this->removeOptionalPublicContent();

        $period = ManagementPeriod::factory()->active()->create([
            'name' => 'Periode Uji Terpadu',
            'start_year' => 2031,
            'end_year' => 2034,
        ]);
        ManagementMember::factory()->for($period, 'period')->create();

        $response = $this->get('/')->assertOk();

        $this->assertGreaterThanOrEqual(2, substr_count($response->getContent(), '2031-2034'));
        $response->assertSee('Masa Bakti');
    }

    public function test_home_does_not_render_the_removed_hero_card(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('hero-visual', false)
            ->assertDontSee('hero-card-bg.webp', false)
            ->assertDontSee('data-image-preview-field="hero_image"', false);
    }

    public function test_home_renders_all_admin_managed_hero_elements(): void
    {
        Agenda::factory()->create();

        FpkProfile::query()->first()->update([
            'hero_eyebrow' => 'Pembuka Hero Uji',
            'hero_title' => 'Judul Hero Uji',
            'hero_subtitle' => 'Subtitle Hero Uji',
            'hero_background_path' => 'profile/background-hero-uji.webp',
            'hero_mobile_background_path' => 'profile/background-hero-mobile-uji.webp',
            'hero_primary_cta_label' => 'Pelajari FPK',
            'hero_secondary_cta_label' => 'Jadwal Kegiatan',
            'hero_legal_basis_label' => 'Payung Hukum',
            'hero_foundation_label' => 'Landasan Organisasi',
            'hero_period_label' => 'Periode Pengurus',
            'institution_legal_basis' => 'Peraturan Uji',
            'institution_foundation' => 'Keputusan Uji',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Pembuka Hero Uji')
            ->assertSee('Judul Hero Uji')
            ->assertSee('Subtitle Hero Uji')
            ->assertSee('Pelajari FPK')
            ->assertSee('Jadwal Kegiatan')
            ->assertSee('Payung Hukum')
            ->assertSee('Landasan Organisasi')
            ->assertSee('Periode Pengurus')
            ->assertSee('profile/background-hero-uji.webp', false)
            ->assertSee('profile/background-hero-mobile-uji.webp', false)
            ->assertSee('data-home-fixed-background', false)
            ->assertSee('data-hero-background="desktop"', false)
            ->assertSee('data-hero-background="mobile"', false)
            ->assertSee('data-hero-background-source="mobile"', false)
            ->assertSee('data-hero-overlay="desktop"', false)
            ->assertSee('data-hero-overlay="mobile"', false)
            ->assertDontSee('data-hero-default-decoration=', false);
    }

    public function test_home_mobile_hero_falls_back_to_desktop_background(): void
    {
        FpkProfile::query()->first()->update([
            'hero_background_path' => 'profile/background-hero-fallback.webp',
            'hero_mobile_background_path' => null,
        ]);

        $response = $this->get('/')
            ->assertOk()
            ->assertSee('data-hero-background-source="desktop-fallback"', false);

        $this->assertSame(
            3,
            substr_count($response->getContent(), 'profile/background-hero-fallback.webp'),
        );

        $response->assertDontSee('data-hero-default-decoration=', false);
    }

    public function test_article_and_agenda_pages_share_the_managed_home_background(): void
    {
        FpkProfile::query()->first()->update([
            'hero_background_path' => 'profile/background-shared-desktop.webp',
            'hero_mobile_background_path' => 'profile/background-shared-mobile.webp',
        ]);

        $article = Article::factory()->create();
        $agenda = Agenda::factory()->create();

        foreach ([
            route('articles.index'),
            route('articles.show', $article),
            route('agendas.show', $agenda),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-public-synced-background', false)
                ->assertSee('profile/background-shared-desktop.webp', false)
                ->assertSee('profile/background-shared-mobile.webp', false)
                ->assertSee('data-hero-background="desktop"', false)
                ->assertSee('data-hero-background="mobile"', false)
                ->assertSee('data-hero-background-source="mobile"', false);
        }
    }

    public function test_article_index_uses_one_unified_background_without_a_light_section(): void
    {
        FpkProfile::query()->first()->update([
            'hero_background_path' => 'profile/background-article-unified.webp',
            'hero_mobile_background_path' => null,
        ]);

        $response = $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('data-articles-unified-background', false)
            ->assertSee('profile/background-article-unified.webp', false)
            ->assertSee('<section class="section bg-transparent">', false)
            ->assertDontSee('<section class="section bg-cream-50">', false);

        $this->assertSame(
            1,
            substr_count($response->getContent(), 'data-public-synced-background'),
        );
    }

    public function test_managed_background_is_preloaded_across_public_navigation_cycle(): void
    {
        FpkProfile::query()->first()->update([
            'hero_background_path' => 'profile/background-cycle-desktop.webp',
            'hero_mobile_background_path' => 'profile/background-cycle-mobile.webp',
        ]);

        $article = Article::factory()->create();
        $agenda = Agenda::factory()->create();

        foreach ([
            route('home'),
            route('articles.index'),
            route('articles.show', $article),
            route('agendas.show', $agenda),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('rel="preload" as="image"', false)
                ->assertSee('data-public-background-preload="desktop"', false)
                ->assertSee('data-public-background-preload="mobile"', false)
                ->assertSee('profile/background-cycle-desktop.webp', false)
                ->assertSee('profile/background-cycle-mobile.webp', false);
        }
    }

    public function test_home_uses_default_dot_decoration_only_without_a_custom_background(): void
    {
        FpkProfile::query()->first()->update([
            'hero_background_path' => null,
            'hero_mobile_background_path' => null,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-home-fixed-background', false)
            ->assertSee('data-hero-default-decoration="desktop"', false)
            ->assertSee('data-hero-default-decoration="mobile"', false)
            ->assertSee('data-hero-overlay="desktop"', false)
            ->assertSee('data-hero-overlay="mobile"', false);
    }

    public function test_home_limits_article_and_agenda_scenes_without_dummy_data(): void
    {
        $this->removeOptionalPublicContent();
        Carbon::setTestNow('2026-07-25 08:00:00');

        try {
            Article::factory()->featured()->create([
                'title' => 'Artikel Unggulan Batas',
                'published_at' => now()->subDays(10),
            ]);

            foreach (range(1, 4) as $index) {
                Article::factory()->create([
                    'title' => 'Artikel Terbaru '.$index,
                    'published_at' => now()->subDays($index),
                ]);
            }

            foreach (range(1, 5) as $index) {
                Agenda::factory()->create([
                    'title' => 'Agenda Terbatas '.$index,
                    'starts_at' => now()->addDays($index),
                    'ends_at' => now()->addDays($index)->addHours(2),
                ]);
            }

            $response = $this->get('/')->assertOk();

            $response
                ->assertSee('Artikel Unggulan Batas')
                ->assertSee('Artikel Terbaru 1')
                ->assertSee('Artikel Terbaru 2')
                ->assertSee('Artikel Terbaru 3')
                ->assertDontSee('Artikel Terbaru 4')
                ->assertSee('Agenda Terbatas 1')
                ->assertSee('Agenda Terbatas 2')
                ->assertSee('Agenda Terbatas 3')
                ->assertSee('Agenda Terbatas 4')
                ->assertDontSee('Agenda Terbatas 5');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_article_index_renders(): void
    {
        $this->get('/artikel')->assertOk();
    }

    public function test_article_navigation_from_other_pages_returns_to_home_section(): void
    {
        Article::factory()->create();

        $response = $this->get(route('articles.index'))->assertOk();
        $articleAnchor = route('home').'#artikel';

        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('href="'.route('home').'"', false);
        $this->assertGreaterThanOrEqual(3, substr_count($response->getContent(), 'href="'.$articleAnchor.'"'));
    }

    public function test_published_article_is_visible(): void
    {
        $article = Article::factory()->create(['title' => 'Artikel Terbit Publik']);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Artikel Terbit Publik');
    }

    public function test_draft_article_returns_404(): void
    {
        $article = Article::factory()->draft()->create();

        $this->get(route('articles.show', $article))->assertNotFound();
    }

    public function test_future_dated_article_returns_404(): void
    {
        $article = Article::factory()->scheduled()->create();

        $this->get(route('articles.show', $article))->assertNotFound();
    }

    public function test_article_search_filters_results(): void
    {
        Article::factory()->create(['title' => 'Pembauran Kebangsaan Malang']);
        Article::factory()->create(['title' => 'Topik Tidak Terkait']);

        $this->get('/artikel?q=Pembauran')
            ->assertOk()
            ->assertSee('Pembauran Kebangsaan Malang')
            ->assertDontSee('Topik Tidak Terkait');
    }

    public function test_home_spotlights_a_featured_article_without_duplicating_it(): void
    {
        Article::factory()->featured()->create([
            'title' => 'Sorotan Kegiatan Unggulan',
            'published_at' => now()->subDay(),
        ]);
        Article::factory()->create([
            'title' => 'Artikel Sekunder Biasa',
            'published_at' => now(),
        ]);

        $response = $this->get('/')->assertOk();

        // Featured badge and both articles are present…
        $response->assertSee('Unggulan');
        $response->assertSee('Sorotan Kegiatan Unggulan');
        $response->assertSee('Artikel Sekunder Biasa');

        // …but the featured article title appears exactly once (no duplication in the secondary list).
        $this->assertSame(1, substr_count($response->getContent(), 'Sorotan Kegiatan Unggulan'));
    }

    public function test_article_index_spotlights_featured_without_duplication(): void
    {
        Article::factory()->featured()->create([
            'title' => 'Sorotan Daftar Artikel',
            'published_at' => now()->subDay(),
        ]);
        Article::factory()->create([
            'title' => 'Artikel Daftar Biasa',
            'published_at' => now(),
        ]);

        $response = $this->get('/artikel')->assertOk();

        $response->assertSee('Unggulan');
        $response->assertSee('Sorotan Daftar Artikel');
        $response->assertSee('Artikel Daftar Biasa');
        $this->assertSame(1, substr_count($response->getContent(), 'Sorotan Daftar Artikel'));
    }

    public function test_home_falls_back_to_latest_article_when_none_featured(): void
    {
        Article::factory()->create(['title' => 'Artikel Terbaru Tanpa Unggulan']);

        $this->get('/')->assertOk()->assertSee('Artikel Terbaru Tanpa Unggulan');
    }

    public function test_published_agenda_is_visible(): void
    {
        $agenda = Agenda::factory()->create(['title' => 'Dialog Kebangsaan']);

        $this->get(route('agendas.show', $agenda))
            ->assertOk()
            ->assertSee('Dialog Kebangsaan');
    }

    public function test_public_agenda_displays_the_effective_time_based_status(): void
    {
        Carbon::setTestNow('2026-07-23 10:00:00');

        try {
            $agenda = Agenda::factory()->create([
                'title' => 'Agenda Status Publik Dinamis',
                'starts_at' => '2026-07-23 09:00:00',
                'ends_at' => '2026-07-23 12:00:00',
                'event_status' => 'scheduled',
            ]);

            $this->get(route('agendas.show', $agenda))
                ->assertOk()
                ->assertSee('Agenda Status Publik Dinamis')
                ->assertSee('Berlangsung');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_completed_agenda_disappears_from_public_pages_but_remains_in_database(): void
    {
        Carbon::setTestNow('2026-07-23 13:00:00');

        try {
            $agenda = Agenda::factory()->create([
                'title' => 'Agenda Selesai Tidak Publik',
                'starts_at' => '2026-07-23 09:00:00',
                'ends_at' => '2026-07-23 12:00:00',
                'event_status' => 'scheduled',
            ]);

            $this->get(route('home'))
                ->assertOk()
                ->assertDontSee('Agenda Selesai Tidak Publik');
            $this->get(route('agendas.show', $agenda))->assertNotFound();
            $this->assertDatabaseHas('agendas', ['id' => $agenda->id]);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_cancelled_agenda_disappears_from_public_pages(): void
    {
        $agenda = Agenda::factory()->create([
            'title' => 'Agenda Dibatalkan Tidak Publik',
            'event_status' => 'cancelled',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Agenda Dibatalkan Tidak Publik');
        $this->get(route('agendas.show', $agenda))->assertNotFound();
    }

    public function test_draft_agenda_returns_404(): void
    {
        $agenda = Agenda::factory()->draft()->create();

        $this->get(route('agendas.show', $agenda))->assertNotFound();
    }

    public function test_sitemap_is_available_as_xml(): void
    {
        Article::factory()->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml');
    }

    public function test_robots_file_allows_public_pages_and_advertises_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *', false)
            ->assertSee('Allow: /', false)
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /login', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_public_layout_exposes_complete_social_and_structured_metadata(): void
    {
        SiteSetting::query()->first()->update([
            'default_og_image_path' => 'branding/og-default.webp',
        ]);
        app()->forgetInstance('fpk.site_setting');

        $response = $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">', false)
            ->assertSee('<meta property="og:locale" content="id_ID">', false)
            ->assertSee('<meta name="twitter:title"', false)
            ->assertSee('<meta name="twitter:description"', false)
            ->assertSee('<meta name="twitter:image"', false)
            ->assertSee(url('/storage/branding/og-default.webp'), false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false);

        $this->assertSame(1, substr_count($response->getContent(), '<meta property="og:image"'));
    }

    public function test_article_uses_one_specific_social_image_and_article_schema(): void
    {
        SiteSetting::query()->first()->update([
            'default_og_image_path' => 'branding/og-default.webp',
        ]);
        app()->forgetInstance('fpk.site_setting');

        $article = Article::factory()->create([
            'title' => 'Artikel SEO Profesional',
            'thumbnail_path' => 'articles/artikel-seo.webp',
        ]);

        $response = $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee(url('/storage/articles/artikel-seo.webp'), false)
            ->assertDontSee(url('/storage/branding/og-default.webp'), false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->assertSame(1, substr_count($response->getContent(), '<meta property="og:image"'));
    }

    public function test_article_search_is_noindex_with_clean_canonical(): void
    {
        $this->get(route('articles.index', ['q' => 'persatuan']))
            ->assertOk()
            ->assertSee('<title>Hasil pencarian artikel</title>', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('articles.index').'">', false);
    }

    public function test_sitemap_excludes_completed_agendas(): void
    {
        Carbon::setTestNow('2026-07-23 13:00:00');

        try {
            $past = Agenda::factory()->create([
                'title' => 'Agenda Lama Sitemap',
                'starts_at' => '2026-07-23 09:00:00',
                'ends_at' => '2026-07-23 12:00:00',
            ]);
            $future = Agenda::factory()->create([
                'title' => 'Agenda Mendatang Sitemap',
                'starts_at' => '2026-07-24 09:00:00',
                'ends_at' => '2026-07-24 12:00:00',
            ]);

            $this->get(route('sitemap'))
                ->assertOk()
                ->assertDontSee(route('agendas.show', $past), false)
                ->assertSee(route('agendas.show', $future), false);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_unknown_page_returns_404(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    private function removeOptionalPublicContent(): void
    {
        Article::query()->delete();
        Agenda::query()->delete();
        ManagementPeriod::query()->update(['is_active' => false]);
        ContactSetting::query()->update([
            'address' => null,
            'phone' => null,
            'whatsapp' => null,
            'email' => null,
            'operational_hours' => null,
            'map_embed_url' => null,
            'instagram_url' => null,
            'facebook_url' => null,
            'youtube_url' => null,
            'tiktok_url' => null,
        ]);
    }
}
