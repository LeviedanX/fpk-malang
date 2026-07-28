<?php

namespace App\Support;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Coordinates database writes with public-disk media changes.
 *
 * New files are staged first and removed on rollback. Obsolete files are only
 * deleted after the database transaction commits, preventing broken references.
 */
class MediaTransaction
{
    /** @var list<string> */
    private array $stagedPaths = [];

    /** @var list<string> */
    private array $obsoletePaths = [];

    public function storeImage(UploadedFile $file, string $directoryKey): string
    {
        $path = ImageStorage::store($file, $directoryKey);
        $this->stagedPaths[] = $path;

        return $path;
    }

    public function replaceImage(
        UploadedFile $file,
        ?string $oldPath,
        string $directoryKey,
    ): string {
        $path = $this->storeImage($file, $directoryKey);
        $this->deleteAfterCommit($oldPath);

        return $path;
    }

    public function storeFile(UploadedFile $file, string $directory, string $disk = 'public'): string
    {
        $path = $file->store($directory, $disk);
        $this->stagedPaths[] = $path;

        return $path;
    }

    public function deleteAfterCommit(?string $path): void
    {
        if ($path) {
            $this->obsoletePaths[] = $path;
        }
    }

    /**
     * @template TResult
     *
     * @param  Closure(): TResult  $callback
     * @return TResult
     */
    public function commit(Closure $callback): mixed
    {
        try {
            $result = DB::transaction($callback);
        } catch (Throwable $exception) {
            $this->deletePaths($this->stagedPaths);

            throw $exception;
        }

        $this->deletePaths($this->obsoletePaths);
        $this->stagedPaths = [];
        $this->obsoletePaths = [];

        return $result;
    }

    /**
     * @param  list<string>  $paths
     */
    private function deletePaths(array $paths): void
    {
        $disk = Storage::disk(config('fpk.uploads.disk', 'public'));

        foreach (array_unique($paths) as $path) {
            $disk->delete($path);
        }
    }
}
