<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Centralises image persistence on the "public" disk: server-generated random
 * file names, stored under a configured sub-directory, with safe replacement and
 * deletion of previous files. Stored images are downscaled and re-encoded when an
 * image driver (GD or Imagick) and intervention/image are available; otherwise the
 * original is kept untouched.
 */
class ImageStorage
{
    private static function disk(): string
    {
        return config('fpk.uploads.disk', 'public');
    }

    /**
     * Store an uploaded image inside one of the configured directories and return
     * its relative path (suitable for Storage::url()).
     */
    public static function store(UploadedFile $file, string $directoryKey): string
    {
        $directory = config("fpk.uploads.directories.{$directoryKey}", $directoryKey);

        // Laravel generates a random, unguessable file name for us.
        $path = $file->store($directory, self::disk());

        self::optimize($path);

        return $path;
    }

    /**
     * Replace an existing image with a new upload, deleting the old file.
     */
    public static function replace(UploadedFile $file, ?string $oldPath, string $directoryKey): string
    {
        $newPath = self::store($file, $directoryKey);

        self::delete($oldPath);

        return $newPath;
    }

    /**
     * Delete a stored file if it exists.
     */
    public static function delete(?string $path): void
    {
        if ($path && Storage::disk(self::disk())->exists($path)) {
            Storage::disk(self::disk())->delete($path);
        }
    }

    /**
     * Downscale oversized images and re-encode to trim file size. No-op when no
     * image driver / library is available, or on any processing error (the
     * validated original is kept in that case).
     */
    private static function optimize(string $path): void
    {
        $maxWidth = (int) config('fpk.uploads.optimize_max_width', 1600);

        $manager = self::imageManager();

        if (! $manager) {
            return;
        }

        try {
            $absolute = Storage::disk(self::disk())->path($path);
            $image = $manager->read($absolute);

            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $image->save($absolute, quality: 82);
        } catch (Throwable) {
            // Keep the original file on any failure.
        }
    }

    private static function imageManager(): ?ImageManager
    {
        if (! class_exists(ImageManager::class)) {
            return null;
        }

        if (extension_loaded('imagick')) {
            return ImageManager::imagick();
        }

        if (extension_loaded('gd')) {
            return ImageManager::gd();
        }

        return null;
    }
}
