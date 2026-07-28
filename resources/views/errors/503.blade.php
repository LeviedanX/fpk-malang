{{--
    Halaman ini sengaja berdiri sendiri: layouts.public diisi oleh view composer
    yang membaca database, sehingga tidak dapat dipakai saat maintenance mode
    atau saat database tidak tersedia.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sedang dalam pemeliharaan &mdash; FPK Kota Malang</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            background: #2e0f12;
            color: #fdfbf6;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            text-align: center;
        }
        .code { font-size: clamp(3.5rem, 12vw, 5.5rem); font-weight: 800; color: #d9a441; margin: 0; letter-spacing: -0.02em; }
        h1 { font-size: clamp(1.25rem, 4vw, 1.75rem); font-weight: 700; margin: 0.75rem 0 0; }
        p { max-width: 34rem; margin: 0.75rem auto 0; color: rgba(250, 245, 234, 0.8); }
        .rule { width: 4rem; height: 4px; border-radius: 999px; background: #c8912f; margin: 1.75rem auto 0; }
    </style>
</head>
<body>
    <main>
        <p class="code">503</p>
        <h1>Sedang dalam pemeliharaan</h1>
        <p>Situs Forum Pembauran Kebangsaan Kota Malang sementara tidak dapat diakses karena pemeliharaan terjadwal. Silakan coba beberapa saat lagi.</p>
        <div class="rule"></div>
    </main>
</body>
</html>
