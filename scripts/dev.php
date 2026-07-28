<?php

/**
 * Launcher server pengembangan.
 *
 * Tugasnya satu: menambahkan php-ini/ milik proyek ke PHP_INI_SCAN_DIR sebelum
 * memanggil concurrently, supaya php-ini/opcache.ini ikut terbaca. Tanpa itu
 * OPcache mati dan setiap request membayar ~330 ms hanya untuk mengompilasi
 * ulang framework.
 *
 * PHP_INI_SCAN_DIR bersifat aditif dan diwariskan ke seluruh proses anak, jadi
 * `php artisan serve` maupun web server bawaan PHP yang ia jalankan (`php -S`,
 * SAPI CLI) sama-sama menerimanya. php.ini global tidak disentuh sama sekali.
 */
$projectIni = realpath(__DIR__.'/../php-ini');

if ($projectIni === false) {
    fwrite(STDERR, "php-ini/ tidak ditemukan; server tetap jalan tanpa OPcache.\n");
} else {
    // Nilai yang sudah ada dipertahankan: mesin ini memakainya untuk mengaktifkan
    // ekstensi lain, dan menimpanya akan mematikan ekstensi tersebut.
    $dirs = array_filter(
        array_map('trim', explode(PATH_SEPARATOR, (string) getenv('PHP_INI_SCAN_DIR'))),
        static fn (string $dir): bool => $dir !== '',
    );
    $dirs[] = $projectIni;

    putenv('PHP_INI_SCAN_DIR='.implode(PATH_SEPARATOR, array_unique($dirs)));
}

$command = 'npx concurrently -c "#93c5fd,#fb7185,#fdba74" '
    .'"php artisan serve" '
    .'"php artisan pail --timeout=0" '
    .'"npm run dev" '
    .'--names=server,logs,vite --kill-others';

passthru($command, $exitCode);

exit($exitCode);
