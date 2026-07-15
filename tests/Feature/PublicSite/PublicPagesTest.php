<?php

namespace Tests\Feature\PublicSite;

use App\Models\Agenda;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('Tentang FPK');
    }

    public function test_article_index_renders(): void
    {
        $this->get('/artikel')->assertOk();
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
}
