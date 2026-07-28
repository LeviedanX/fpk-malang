<?php

namespace Tests\Feature;

use App\Support\MediaTransaction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class MediaTransactionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_new_file_is_removed_and_old_file_is_preserved_when_database_work_fails(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('articles/old.jpg', 'old');
        $media = new MediaTransaction;
        $newPath = $media->replaceImage(
            UploadedFile::fake()->createWithContent('new.jpg', 'new'),
            'articles/old.jpg',
            'articles',
        );

        try {
            $media->commit(fn () => throw new RuntimeException('simulated database failure'));
            $this->fail('Media transaction should propagate the database failure.');
        } catch (RuntimeException $exception) {
            $this->assertSame('simulated database failure', $exception->getMessage());
        }

        Storage::disk('public')->assertExists('articles/old.jpg');
        Storage::disk('public')->assertMissing($newPath);
    }

    public function test_old_file_is_deleted_only_after_database_work_succeeds(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('articles/old.jpg', 'old');
        $media = new MediaTransaction;
        $newPath = $media->replaceImage(
            UploadedFile::fake()->createWithContent('new.jpg', 'new'),
            'articles/old.jpg',
            'articles',
        );

        $media->commit(fn () => true);

        Storage::disk('public')->assertMissing('articles/old.jpg');
        Storage::disk('public')->assertExists($newPath);
    }
}
