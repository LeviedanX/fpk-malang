<?php

namespace Tests\Feature\Admin;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArticleCrudTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A real (tiny) PNG upload, so the test does not depend on the GD extension
     * being available to generate a fake image.
     */
    private function pngUpload(string $name = 'thumb.png'): UploadedFile
    {
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk'
            .'+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        $path = tempnam(sys_get_temp_dir(), 'png');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    public function test_admin_can_create_a_published_article_with_thumbnail(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('admin.articles.store'), [
            'title' => 'Artikel Uji Coba',
            'slug' => '',
            'excerpt' => 'Ringkasan singkat.',
            'body' => '<p>Isi artikel.</p><script>alert(1)</script>',
            'status' => 'published',
            'published_at' => '',
            'thumbnail' => $this->pngUpload(),
        ]);

        $response->assertRedirect(route('admin.articles.index'));

        $article = Article::firstWhere('title', 'Artikel Uji Coba');

        $this->assertNotNull($article);
        $this->assertSame('artikel-uji-coba', $article->slug);
        $this->assertNotNull($article->published_at, 'Published article should receive a publish time.');
        $this->assertStringNotContainsString('<script>', $article->body, 'Body must be sanitized.');
        $this->assertStringContainsString('Isi artikel', $article->body);
        $this->assertNotNull($article->thumbnail_path);
        Storage::disk('public')->assertExists($article->thumbnail_path);
    }

    public function test_slug_must_be_unique(): void
    {
        $user = User::factory()->create();
        Article::factory()->create(['slug' => 'duplikat']);

        $this->actingAs($user)->post(route('admin.articles.store'), [
            'title' => 'Judul Lain',
            'slug' => 'duplikat',
            'body' => '<p>Isi.</p>',
            'status' => 'draft',
        ])->assertSessionHasErrors('slug');
    }

    public function test_admin_can_update_an_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['title' => 'Judul Lama']);

        $this->actingAs($user)->put(route('admin.articles.update', $article), [
            'title' => 'Judul Baru',
            'slug' => $article->slug,
            'body' => '<p>Diperbarui.</p>',
            'status' => 'draft',
        ])->assertRedirect(route('admin.articles.index'));

        $this->assertSame('Judul Baru', $article->fresh()->title);
    }

    public function test_admin_can_mark_an_article_as_featured(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.articles.store'), [
            'title' => 'Artikel Unggulan',
            'slug' => '',
            'body' => '<p>Isi.</p>',
            'status' => 'published',
            'is_featured' => '1',
        ])->assertRedirect(route('admin.articles.index'));

        $this->assertTrue(Article::firstWhere('title', 'Artikel Unggulan')->is_featured);
    }

    public function test_featured_flag_defaults_to_false_when_unchecked(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('admin.articles.store'), [
            'title' => 'Artikel Biasa',
            'slug' => '',
            'body' => '<p>Isi.</p>',
            'status' => 'published',
            'is_featured' => '0',
        ]);

        $this->assertFalse(Article::firstWhere('title', 'Artikel Biasa')->is_featured);
    }

    public function test_admin_can_soft_delete_an_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        $this->actingAs($user)->delete(route('admin.articles.destroy', $article))
            ->assertRedirect(route('admin.articles.index'));

        $this->assertSoftDeleted($article);
    }
}
