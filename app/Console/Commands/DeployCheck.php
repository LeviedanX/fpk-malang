<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DeployCheck extends Command
{
    protected $signature = 'deploy:check';

    protected $description = 'Memeriksa konfigurasi minimum sebelum aplikasi dipublikasikan';

    public function handle(): int
    {
        $failures = [];

        if (PHP_VERSION_ID < 80300) {
            $failures[] = 'PHP minimal versi 8.3.';
        }

        foreach (['ctype', 'dom', 'fileinfo', 'intl', 'mbstring', 'openssl', 'pdo', 'pdo_mysql', 'session', 'tokenizer', 'gd'] as $extension) {
            if (! extension_loaded($extension)) {
                $failures[] = "Ekstensi PHP {$extension} wajib aktif.";
            }
        }

        if (! extension_loaded('Zend OPcache') && ! extension_loaded('opcache')) {
            $failures[] = 'PHP OPcache wajib aktif di production.';
        }

        if (filter_var((string) ini_get('expose_php'), FILTER_VALIDATE_BOOLEAN)) {
            $failures[] = 'PHP expose_php harus Off agar versi runtime tidak dibocorkan melalui header.';
        }

        if (! app()->environment('production')) {
            $failures[] = 'APP_ENV harus production.';
        }

        if ((bool) config('app.debug')) {
            $failures[] = 'APP_DEBUG harus false.';
        }

        $appUrl = (string) config('app.url');
        if (! str_starts_with($appUrl, 'https://')) {
            $failures[] = 'APP_URL harus menggunakan HTTPS.';
        }

        if (str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
            $failures[] = 'APP_URL tidak boleh memakai host lokal.';
        }

        if (! $this->validApplicationKey()) {
            $failures[] = 'APP_KEY belum valid.';
        }

        if ((string) config('database.default') !== 'mysql') {
            $failures[] = 'DB_CONNECTION harus mysql.';
        }

        $username = mb_strtolower((string) config('database.connections.mysql.username'));
        if ($username === '' || in_array($username, ['root', 'admin'], true)) {
            $failures[] = 'DB_USERNAME harus berupa user aplikasi dengan privilege minimal, bukan root/admin.';
        }

        if ((string) config('admin.initial.password') !== '') {
            $failures[] = 'Hapus ADMIN_PASSWORD dari environment setelah provisioning admin.';
        }

        if (is_file(public_path('hot'))) {
            $failures[] = 'public/hot harus dihapus dari deployment produksi.';
        }

        $warnings = [];

        if (trim((string) config('app.trusted_proxies')) === '') {
            $warnings[] = 'TRUSTED_PROXIES kosong. Bila aplikasi berada di belakang reverse proxy/CDN, '
                .'HSTS tidak akan terkirim, rate limit login memakai IP proxy, dan log admin mencatat IP proxy.';
        }

        if (! app()->configurationIsCached()) {
            $failures[] = 'Konfigurasi production belum di-cache; jalankan php artisan optimize.';
        }

        if (! app()->routesAreCached()) {
            $failures[] = 'Route production belum di-cache; jalankan php artisan optimize.';
        }

        if (! (bool) config('session.encrypt')) {
            $failures[] = 'SESSION_ENCRYPT harus true.';
        }

        if (! (bool) config('session.secure')) {
            $failures[] = 'SESSION_SECURE_COOKIE harus true.';
        }

        if (! in_array((string) config('session.same_site'), ['strict', 'lax'], true)) {
            $failures[] = 'SESSION_SAME_SITE harus strict atau lax.';
        }

        if ((string) config('filesystems.default') !== 'public') {
            $failures[] = 'FILESYSTEM_DISK harus public.';
        }

        if (! $this->storageLinkIsValid()) {
            $failures[] = 'public/storage belum terhubung ke storage/app/public.';
        }

        foreach ([storage_path(), storage_path('framework'), storage_path('logs'), base_path('bootstrap/cache')] as $path) {
            if (! is_dir($path) || ! is_writable($path)) {
                $failures[] = "Direktori runtime tidak dapat ditulis: {$path}";
            }
        }

        try {
            DB::select('SELECT 1');
            $this->checkDatabaseGrants($failures);
            $this->checkPendingMigrations($failures);
        } catch (Throwable) {
            $failures[] = 'Koneksi database gagal.';
        }

        $probe = '.health/deploy-check-'.bin2hex(random_bytes(6));

        try {
            $disk = Storage::disk(config('filesystems.default'));
            if (! $disk->put($probe, 'ok')) {
                $failures[] = 'Storage aplikasi tidak dapat ditulis.';
            }
            $disk->delete($probe);
        } catch (Throwable) {
            $failures[] = 'Storage aplikasi tidak dapat ditulis.';
        }

        foreach (array_unique($warnings) as $warning) {
            $this->warn($warning);
        }

        if ($failures !== []) {
            foreach (array_unique($failures) as $failure) {
                $this->error($failure);
            }

            return self::FAILURE;
        }

        $this->info('Pemeriksaan deployment lulus.');

        return self::SUCCESS;
    }

    private function validApplicationKey(): bool
    {
        $key = (string) config('app.key');

        if (str_starts_with($key, 'base64:')) {
            $key = base64_decode(substr($key, 7), true) ?: '';
        }

        return Encrypter::supported($key, (string) config('app.cipher'));
    }

    /**
     * @param  list<string>  $failures
     */
    private function checkDatabaseGrants(array &$failures): void
    {
        $grants = collect(DB::select('SHOW GRANTS FOR CURRENT_USER()'))
            ->flatMap(fn (object $row): array => array_values((array) $row))
            ->map(fn (mixed $grant): string => mb_strtoupper((string) $grant));

        if ($grants->contains(
            fn (string $grant): bool => str_contains($grant, 'ALL PRIVILEGES ON *.*')
                || str_contains($grant, 'WITH GRANT OPTION')
        )) {
            $failures[] = 'User database memiliki privilege global/grant option; gunakan least privilege khusus database aplikasi.';
        }
    }

    /**
     * @param  list<string>  $failures
     */
    private function checkPendingMigrations(array &$failures): void
    {
        /** @var Migrator $migrator */
        $migrator = app('migrator');

        if (! $migrator->repositoryExists()) {
            $failures[] = 'Tabel migration belum tersedia.';

            return;
        }

        $files = $migrator->getMigrationFiles(database_path('migrations'));
        $pending = array_diff(array_keys($files), $migrator->getRepository()->getRan());

        if ($pending !== []) {
            $failures[] = 'Masih ada migration yang belum dijalankan: '.count($pending).'.';
        }
    }

    private function storageLinkIsValid(): bool
    {
        $publicStorage = realpath(public_path('storage'));
        $storageTarget = realpath(storage_path('app/public'));

        return $publicStorage !== false
            && $storageTarget !== false
            && $publicStorage === $storageTarget;
    }
}
