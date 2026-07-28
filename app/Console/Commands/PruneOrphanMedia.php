<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Melaporkan (dan opsional menghapus) berkas pada disk publik yang sudah tidak
 * direferensikan baris mana pun.
 *
 * Alur unggah normal sudah rapi: MediaTransaction menghapus berkas lama setelah
 * commit dan membatalkan berkas baru saat transaksi gagal. Sisa yatim tetap bisa
 * muncul dari data lama, impor manual, atau proses yang terbunuh di tengah
 * jalan — dan sebelumnya tidak ada cara untuk mengambilnya kembali.
 *
 * Default-nya hanya melapor. Penghapusan wajib diminta eksplisit via --delete.
 */
class PruneOrphanMedia extends Command
{
    protected $signature = 'media:prune-orphans
        {--delete : Hapus berkas yatim (tanpa opsi ini perintah hanya melapor)}
        {--min-age=60 : Abaikan berkas yang lebih baru dari sekian menit}';

    protected $description = 'Menemukan berkas pada disk publik yang tidak direferensikan database';

    /**
     * Kolom penyimpan path media per tabel.
     *
     * @var array<string, list<string>>
     */
    private const MEDIA_COLUMNS = [
        'site_settings' => [
            'logo_path',
            'favicon_path',
            'default_og_image_path',
            'background_music_path',
            'admin_login_background_path',
        ],
        'fpk_profiles' => [
            'hero_background_path',
            'hero_mobile_background_path',
            'about_image_path',
        ],
        'management_periods' => ['group_photo_path'],
        'management_members' => ['portrait_path'],
        'gallery_images' => ['image_path'],
        'articles' => ['thumbnail_path'],
        'agendas' => ['poster_path'],
        'chat_messages' => ['attachment_path'],
    ];

    public function handle(): int
    {
        $disk = Storage::disk(config('fpk.uploads.disk', 'public'));
        $minAgeMinutes = max(0, (int) $this->option('min-age'));
        $cutoff = now()->subMinutes($minAgeMinutes)->getTimestamp();

        $referenced = $this->referencedPaths();

        $orphans = collect($disk->allFiles())
            ->reject(fn (string $path): bool => str_starts_with(basename($path), '.'))
            ->reject(fn (string $path): bool => $referenced->has($path))
            // Berkas yang baru saja ditulis bisa jadi milik unggahan yang
            // transaksinya belum commit; jangan sentuh. Perbandingan memakai
            // <= agar --min-age=0 berarti "tanpa syarat umur", termasuk berkas
            // yang ditulis pada detik yang sama.
            ->filter(fn (string $path): bool => $disk->lastModified($path) <= $cutoff)
            ->values();

        if ($orphans->isEmpty()) {
            $this->info('Tidak ada berkas yatim. Disk publik sejalan dengan database.');

            return self::SUCCESS;
        }

        $bytes = $orphans->sum(fn (string $path): int => $disk->size($path));
        $this->warn("{$orphans->count()} berkas yatim ditemukan (".$this->humanBytes($bytes).'):');

        foreach ($orphans as $path) {
            $this->line('  '.$path);
        }

        if (! $this->option('delete')) {
            $this->newLine();
            $this->comment('Mode laporan. Jalankan ulang dengan --delete untuk menghapus.');

            return self::SUCCESS;
        }

        // Baca ulang referensi tepat sebelum menghapus: unggahan bisa saja
        // commit setelah pemindaian pertama.
        $referenced = $this->referencedPaths();
        $deleted = 0;

        foreach ($orphans as $path) {
            if ($referenced->has($path)) {
                $this->line("  dilewati (sudah dipakai): {$path}");

                continue;
            }

            $disk->delete($path);
            $deleted++;
        }

        $this->info("{$deleted} berkas yatim dihapus.");

        return self::SUCCESS;
    }

    /**
     * @return Collection<string, true>
     */
    private function referencedPaths(): Collection
    {
        $paths = [];

        foreach (self::MEDIA_COLUMNS as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                // withTrashed: baris yang diarsipkan masih memiliki gambarnya dan
                // dapat dipulihkan, jadi berkasnya belum boleh dianggap yatim.
                DB::table($table)
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->pluck($column)
                    ->each(function (string $path) use (&$paths): void {
                        $paths[$path] = true;
                    });
            }
        }

        return collect($paths);
    }

    private function humanBytes(int $bytes): string
    {
        foreach (['B', 'KB', 'MB', 'GB'] as $unit) {
            if ($bytes < 1024 || $unit === 'GB') {
                return round($bytes, 1).' '.$unit;
            }

            $bytes = (int) ($bytes / 1024);
        }

        return $bytes.' B';
    }
}
