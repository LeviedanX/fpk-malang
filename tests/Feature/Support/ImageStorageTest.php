<?php

namespace Tests\Feature\Support;

use App\Support\ImageStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageStorageTest extends TestCase
{
    public function test_image_upload_uses_webp_when_gd_is_available_and_safely_falls_back_otherwise(): void
    {
        Storage::fake('public');

        $temporaryPath = tempnam(sys_get_temp_dir(), 'fpk-image-');
        file_put_contents(
            $temporaryPath,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAFUlEQVR42mNkYGD4z8DAwMgABYwMDAAANwUBAO6f4iQAAAAASUVORK5CYII='),
        );

        $upload = new UploadedFile($temporaryPath, 'gambar-uji.png', 'image/png', null, true);
        $storedPath = ImageStorage::store($upload, 'articles');

        Storage::disk('public')->assertExists($storedPath);

        if (function_exists('imagewebp')) {
            $this->assertStringEndsWith('.webp', $storedPath);
            Storage::disk('public')->assertMissing(
                preg_replace('/\.webp$/', '.png', $storedPath),
            );
        } else {
            $this->assertStringEndsWith('.png', $storedPath);
        }
    }
}
