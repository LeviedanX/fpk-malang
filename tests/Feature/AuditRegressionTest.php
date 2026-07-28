<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Article;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresi untuk cacat yang ditemukan pada audit menyeluruh 28 Juli 2026.
 * Setiap test di sini gagal pada kode sebelum perbaikan.
 */
class AuditRegressionTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * AgendaFactory::past() sempat memundurkan jadwal tanpa ikut memundurkan
     * published_at, sehingga melanggar agendas_publication_timestamp_check dan
     * membuat DemoContentSeeder berhenti di tengah jalan.
     */
    public function test_past_agenda_factory_state_satisfies_publication_timestamp_check(): void
    {
        $agenda = Agenda::factory()->past()->create();

        $this->assertNotNull($agenda->published_at);
        $this->assertTrue(
            $agenda->published_at->lessThanOrEqualTo($agenda->ends_at),
            'published_at harus berada pada atau sebelum ends_at.',
        );
    }

    public function test_demo_agenda_states_can_all_be_persisted(): void
    {
        $this->assertCount(3, Agenda::factory()->count(3)->past()->create());
        $this->assertCount(2, Agenda::factory()->count(2)->create());
        $this->assertNull(Agenda::factory()->draft()->create()->published_at);
    }

    /**
     * Indeks unik pada slug ikut menghitung baris soft-deleted, sehingga admin
     * bisa terhalang oleh artikel yang hanya terlihat di Arsip. Pesan galat
     * wajib menyebutkan Arsip agar penyebabnya dapat ditelusuri.
     */
    public function test_article_slug_conflict_with_archived_row_explains_the_archive(): void
    {
        $user = User::factory()->create();
        $archived = Article::factory()->create(['title' => 'Judul Terarsip']);
        $slug = $archived->slug;
        $archived->delete();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.articles.create'))
            ->post(route('admin.articles.store'), [
                'title' => 'Judul Baru',
                'slug' => $slug,
                'body' => 'Isi artikel.',
                'status' => 'draft',
            ]);

        $response->assertSessionHasErrors('slug');
        $this->assertStringContainsString(
            'Arsip',
            (string) session('errors')->first('slug'),
        );
    }

    public function test_agenda_slug_conflict_with_archived_row_explains_the_archive(): void
    {
        $user = User::factory()->create();
        $archived = Agenda::factory()->create(['title' => 'Agenda Terarsip']);
        $slug = $archived->slug;
        $archived->delete();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.agendas.create'))
            ->post(route('admin.agendas.store'), [
                'title' => 'Agenda Baru',
                'slug' => $slug,
                'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
                'event_status' => 'scheduled',
                'publication_status' => 'draft',
            ]);

        $response->assertSessionHasErrors('slug');
        $this->assertStringContainsString(
            'Arsip',
            (string) session('errors')->first('slug'),
        );
    }

    /**
     * Slug yang masih dipakai baris aktif tetap ditolak, tetapi tanpa menyebut
     * Arsip supaya pesannya tidak menyesatkan.
     */
    public function test_article_slug_conflict_with_live_row_is_still_rejected(): void
    {
        $user = User::factory()->create();
        $live = Article::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('admin.articles.create'))
            ->post(route('admin.articles.store'), [
                'title' => 'Judul Baru',
                'slug' => $live->slug,
                'body' => 'Isi artikel.',
                'status' => 'draft',
            ]);

        $response->assertSessionHasErrors('slug');
        $this->assertStringNotContainsString(
            'Arsip',
            (string) session('errors')->first('slug'),
        );
    }

    /**
     * Pengurutan galeri memakai query builder, yang tidak memicu event `saved`,
     * sehingga cache visibilitas konten publik harus dibersihkan manual. Tanpa
     * itu navbar masih menautkan section Galeri setelah seluruh foto disembunyikan.
     */
    public function test_hiding_every_gallery_image_clears_public_visibility_cache(): void
    {
        $user = User::factory()->create();
        GalleryImage::query()->delete();

        $image = GalleryImage::create([
            'image_path' => 'gallery/regression.webp',
            'display_order' => 10,
            'is_visible' => true,
        ]);

        Cache::forget('fpk.public_content_visibility');
        $this->assertTrue(app('fpk.public_content_visibility')['gallery']);

        $this
            ->actingAs($user)
            ->put(route('admin.galleries.update'), [
                'items' => [
                    $image->id => ['display_order' => 10, 'is_visible' => 0],
                ],
            ])
            ->assertRedirect(route('admin.galleries.index'));

        $this->assertFalse(
            Cache::has('fpk.public_content_visibility'),
            'Cache visibilitas harus dibersihkan setelah galeri diperbarui.',
        );
    }

    /** Halaman login wajib punya satu h1 agar hierarki heading tidak terputus. */
    public function test_login_page_exposes_a_single_top_level_heading(): void
    {
        $html = $this->get(route('login'))->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1\b/i', (string) $html));
    }

    /** Tombol Back to Top tersedia di layout publik beserta atribut aksesibilitasnya. */
    public function test_public_layout_ships_an_accessible_back_to_top_control(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-back-to-top', escape: false)
            ->assertSee('aria-label="Kembali ke atas"', escape: false)
            ->assertSee('type="button"', escape: false);
    }

    /**
     * media:prune-orphans hanya boleh menyentuh berkas yang benar-benar tidak
     * direferensikan, dan tanpa --delete tidak boleh menghapus apa pun.
     */
    public function test_orphan_media_pruner_never_touches_referenced_files(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('gallery/dipakai.webp', 'x');
        Storage::disk('public')->put('gallery/yatim.webp', 'x');
        GalleryImage::create([
            'image_path' => 'gallery/dipakai.webp',
            'display_order' => 10,
            'is_visible' => true,
        ]);

        $this->artisan('media:prune-orphans', ['--min-age' => 0])->assertSuccessful();
        Storage::disk('public')->assertExists('gallery/dipakai.webp');
        Storage::disk('public')->assertExists('gallery/yatim.webp');

        $this->artisan('media:prune-orphans', ['--delete' => true, '--min-age' => 0])
            ->assertSuccessful();
        Storage::disk('public')->assertExists('gallery/dipakai.webp');
        Storage::disk('public')->assertMissing('gallery/yatim.webp');
    }

    /** Berkas yang baru diunggah dilindungi agar tidak menabrak transaksi berjalan. */
    public function test_orphan_media_pruner_spares_recently_written_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('gallery/baru.webp', 'x');

        $this->artisan('media:prune-orphans', ['--delete' => true])->assertSuccessful();

        Storage::disk('public')->assertExists('gallery/baru.webp');
    }

    /** Tombol aksi form admin tidak boleh bergantung pada animasi reveal. */
    public function test_admin_form_action_buttons_do_not_depend_on_reveal_animation(): void
    {
        $user = User::factory()->create();

        foreach ([
            route('admin.articles.create'),
            route('admin.agendas.create'),
            route('admin.periods.create'),
            route('admin.members.create'),
        ] as $url) {
            $html = (string) $this->actingAs($user)->get($url)->assertOk()->getContent();

            $this->assertSame(
                0,
                preg_match_all('/<div class="reveal[^"]*"[^>]*>\s*<button type="submit"/', $html),
                "Tombol submit pada {$url} masih memakai class reveal.",
            );
        }
    }
}
