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
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Tentang FPK');
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

    public function test_home_renders_group_photo_and_swipeable_member_cards(): void
    {
        $this->removeOptionalPublicContent();

        $period = ManagementPeriod::factory()->active()->create([
            'group_photo_path' => 'management/foto-bersama.webp',
        ]);

        ManagementMember::factory()->for($period, 'period')->create([
            'name' => 'Ketua Pengurus Uji',
            'position' => 'Ketua',
            'portrait_path' => 'management/ketua.webp',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Foto bersama pengurus Forum Pembauran Kebangsaan Kota Malang', false)
            ->assertSee('data-member-carousel', false)
            ->assertSee('Ketua Pengurus Uji')
            ->assertSee('management/ketua.webp', false);
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

    public function test_custom_hero_keeps_dynamic_logo_organization_name_and_tagline_visible(): void
    {
        $site = SiteSetting::query()->first();
        $site->update([
            'organization_name' => 'Organisasi FPK Dinamis',
            'tagline' => 'Tagline dinamis dari pengaturan',
            'logo_path' => 'branding/logo-dinamis.webp',
        ]);

        $profile = FpkProfile::query()->first();
        $profile->update(['hero_image_path' => 'profile/hero-dinamis.webp']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Organisasi FPK Dinamis')
            ->assertSee('Tagline dinamis dari pengaturan')
            ->assertSee('branding/logo-dinamis.webp', false)
            ->assertSee('profile/hero-dinamis.webp', false);
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

    public function test_unknown_page_returns_404(): void
    {
        $this->get('/halaman-tidak-ada')->assertNotFound();
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
