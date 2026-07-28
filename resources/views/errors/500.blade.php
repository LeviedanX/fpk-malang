{{--
    Halaman ini sengaja berdiri sendiri: layouts.public diisi oleh view composer
    yang membaca database. Memakai layout tersebut akan gagal kembali ketika
    penyebab error 500 justru database atau storage.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Terjadi kesalahan &mdash; FPK Kota Malang</title>
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
        .home {
            display: inline-block;
            margin-top: 1.75rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            background: #c8912f;
            color: #2e0f12;
            font-weight: 600;
            text-decoration: none;
        }
        .home:hover { background: #d9a441; }
    </style>
</head>
<body>
    <main>
        <p class="code">500</p>
        <h1>Terjadi kesalahan pada server</h1>
        <p>Maaf, permintaan Anda tidak dapat diproses saat ini. Tim pengelola telah dicatat mengenai gangguan ini. Silakan coba beberapa saat lagi.</p>
        <a class="home" href="/">Kembali ke Beranda</a>
    </main>
</body>
</html>
