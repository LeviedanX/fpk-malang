<?php

namespace Tests\Feature\Admin;

use App\Models\Agenda;
use App\Models\Article;
use App\Models\ContactSetting;
use App\Models\GalleryImage;
use App\Models\ManagementPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Setiap skenario galeri harus independen dari data lokal yang sudah
        // ada; transaksi akan mengembalikan data tersebut setelah test selesai.
        GalleryImage::query()->delete();
    }

    private function pngUpload(string $name): UploadedFile
    {
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk'
            .'+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
        );

        $path = tempnam(sys_get_temp_dir(), 'gallery-png');
        file_put_contents($path, $bytes);

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    public function test_admin_can_upload_order_hide_and_delete_gallery_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('admin.galleries.store'), [
                'images' => [
                    $this->pngUpload('galeri-satu.png'),
                    $this->pngUpload('galeri-dua.png'),
                ],
            ])
            ->assertRedirect(route('admin.galleries.index'))
            ->assertSessionDoesntHaveErrors();

        $galleryImages = GalleryImage::query()->ordered()->get();

        $this->assertCount(2, $galleryImages);
        Storage::disk('public')->assertExists($galleryImages[0]->image_path);
        Storage::disk('public')->assertExists($galleryImages[1]->image_path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-public-gallery', false)
            ->assertSee(Storage::url($galleryImages[0]->image_path), false)
            ->assertSee(Storage::url($galleryImages[1]->image_path), false)
            ->assertDontSee('data-gallery-caption', false);

        $this->actingAs($user)
            ->put(route('admin.galleries.update'), [
                'items' => [
                    $galleryImages[0]->id => [
                        'display_order' => 30,
                        'is_visible' => 0,
                    ],
                    $galleryImages[1]->id => [
                        'display_order' => 5,
                        'is_visible' => 1,
                    ],
                ],
            ])
            ->assertRedirect(route('admin.galleries.index'))
            ->assertSessionDoesntHaveErrors();

        $this->assertFalse($galleryImages[0]->fresh()->is_visible);
        $this->assertSame(5, $galleryImages[1]->fresh()->display_order);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(Storage::url($galleryImages[0]->image_path), false)
            ->assertSee(Storage::url($galleryImages[1]->image_path), false);

        $visiblePath = $galleryImages[1]->image_path;

        $this->actingAs($user)
            ->delete(route('admin.galleries.destroy', $galleryImages[1]))
            ->assertRedirect(route('admin.galleries.index'));

        $this->assertDatabaseMissing('gallery_images', ['id' => $galleryImages[1]->id]);
        Storage::disk('public')->assertMissing($visiblePath);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('data-public-gallery', false);
    }

    public function test_home_sections_and_navigation_follow_the_institutional_content_order(): void
    {
        Article::factory()->featured()->create();
        Agenda::factory()->create();
        GalleryImage::create([
            'image_path' => 'gallery/urutan-uji.webp',
            'display_order' => 10,
            'is_visible' => true,
        ]);
        $activePeriod = ManagementPeriod::query()->active()->first()
            ?? ManagementPeriod::factory()->active()->create();
        $activePeriod->update([
            'group_photo_path' => 'management/pengurus-uji.webp',
        ]);

        $contact = ContactSetting::query()->first() ?? new ContactSetting;
        $contact->email = 'kontak@fpkmalang.test';
        $contact->save();
        app()->forgetInstance('fpk.contact_setting');

        $content = $this->get(route('home'))->assertOk()->getContent();
        $markers = [
            'id="tentang"',
            'id="agenda"',
            'id="galeri"',
            'id="artikel"',
            'id="pengurus"',
            'id="kontak"',
        ];

        $previousPosition = -1;

        foreach ($markers as $marker) {
            $position = strpos($content, $marker);
            $this->assertNotFalse($position, "Marker {$marker} tidak ditemukan.");
            $this->assertGreaterThan($previousPosition, $position, "Urutan {$marker} tidak sesuai.");
            $previousPosition = $position;
        }

        $navigationStart = strpos($content, 'aria-label="Navigasi utama"');
        $navigationEnd = strpos($content, '</nav>', $navigationStart);
        $navigation = substr($content, $navigationStart, $navigationEnd - $navigationStart);

        foreach (['#tentang', '#agenda', '#galeri', '#artikel', '#pengurus', '#kontak'] as $href) {
            $this->assertStringContainsString($href, $navigation);
        }
    }

    public function test_gallery_rejects_non_image_uploads(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create())
            ->post(route('admin.galleries.store'), [
                'images' => [
                    UploadedFile::fake()->create('susupan.php', 10, 'application/x-php'),
                ],
            ])
            ->assertSessionHasErrors('images.0');

        $this->assertDatabaseCount('gallery_images', 0);
    }
}
