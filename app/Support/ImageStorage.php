<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Centralises image persistence on the "public" disk: server-generated random
 * file names and stored under a configured sub-directory. Stored images are downscaled and re-encoded to
 * WebP when GD is available; otherwise the validated original is kept untouched.
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

        return self::optimize($path, $directoryKey);
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
     * Downscale oversized images and re-encode to WebP. No-op when GD/WebP is
     * unavailable or on any processing error (the original remains intact).
     */
    private static function optimize(string $path, string $directoryKey): string
    {
        if (! function_exists('imagecreatefromstring') || ! function_exists('imagewebp')) {
            return $path;
        }

        $source = null;
        $output = null;

        try {
            $disk = Storage::disk(self::disk());
            $source = @imagecreatefromstring($disk->get($path));

            if ($source === false) {
                return $path;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $maxWidth = self::optimizedMaxWidth($directoryKey, $width, $height);
            $output = $source;

            if ($width > $maxWidth) {
                $targetHeight = max(1, (int) round($height * $maxWidth / $width));
                $output = imagecreatetruecolor($maxWidth, $targetHeight);
                imagealphablending($output, false);
                imagesavealpha($output, true);
                $transparent = imagecolorallocatealpha($output, 0, 0, 0, 127);
                imagefill($output, 0, 0, $transparent);
                imagecopyresampled(
                    $output,
                    $source,
                    0,
                    0,
                    0,
                    0,
                    $maxWidth,
                    $targetHeight,
                    $width,
                    $height,
                );
            }

            $directory = pathinfo($path, PATHINFO_DIRNAME);
            $filename = pathinfo($path, PATHINFO_FILENAME).'.webp';
            $optimizedPath = ($directory === '.' ? '' : "{$directory}/").$filename;
            $quality = (int) config('fpk.uploads.optimize_quality', 80);

            if (! imagewebp($output, $disk->path($optimizedPath), $quality)
                || ! $disk->exists($optimizedPath)
                || $disk->size($optimizedPath) === 0) {
                return $path;
            }

            if ($optimizedPath !== $path) {
                $disk->delete($path);
            }

            return $optimizedPath;
        } catch (Throwable) {
            return $path;
        } finally {
            if ($output !== null && $output !== $source) {
                imagedestroy($output);
            }

            if ($source !== null && $source !== false) {
                imagedestroy($source);
            }
        }
    }

    private static function optimizedMaxWidth(string $directoryKey, int $width, int $height): int
    {
        if ($directoryKey === 'management') {
            return $width >= $height ? 1600 : 720;
        }

        return (int) config(
            "fpk.uploads.optimize_max_widths.{$directoryKey}",
            config('fpk.uploads.optimize_max_width', 1600),
        );
    }
}
