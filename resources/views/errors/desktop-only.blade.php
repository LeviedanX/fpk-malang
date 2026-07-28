{{-- Ditampilkan ketika panel admin diakses dari ponsel atau tablet.

     Sengaja berdiri sendiri, tidak memakai layouts.admin maupun layouts.public:
     halaman ini harus tetap tampil benar tanpa bergantung pada komposer view
     atau navigasi milik area yang justru sedang diblokir. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Perlu Perangkat Desktop</title>
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        :root { color-scheme: dark; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 1.5rem;
            background: radial-gradient(circle at 50% 0%, #4f1c20, #2e0f12 70%);
            color: #faf5ea;
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
            line-height: 1.6;
            text-align: center;
        }
        .card {
            max-width: 26rem;
            border: 1px solid rgba(217, 164, 65, 0.3);
            border-radius: 1.25rem;
            background: rgba(0, 0, 0, 0.25);
            padding: 2rem 1.5rem;
        }
        .icon {
            display: grid;
            place-items: center;
            width: 4rem;
            height: 4rem;
            margin: 0 auto 1.25rem;
            border-radius: 9999px;
            border: 1px solid rgba(217, 164, 65, 0.45);
            background: rgba(217, 164, 65, 0.12);
            color: #d9a441;
        }
        .icon svg { width: 2rem; height: 2rem; }
        h1 { font-size: 1.3rem; font-weight: 700; margin-bottom: 0.6rem; }
        p { font-size: 0.9rem; color: rgba(250, 245, 234, 0.75); }
        .hint {
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.8rem;
            color: rgba(250, 245, 234, 0.55);
        }
        .back {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.6rem 1.4rem;
            border-radius: 9999px;
            background: linear-gradient(140deg, #d9a441, #c8912f);
            color: #2e0f12;
            font-size: 0.85rem;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main class="card">
        <span class="icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"
                stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="3" width="20" height="14" rx="2" />
                <path d="M8 21h8M12 17v4" />
            </svg>
        </span>

        <h1>Panel admin hanya untuk desktop</h1>
        <p>
            Halaman administrator tidak dapat dibuka dari ponsel atau tablet.
            Silakan gunakan komputer desktop atau laptop untuk mengelola website.
        </p>

        <p class="hint">
            Jika Anda memang sedang memakai komputer, pastikan browser tidak
            sedang dalam mode tampilan seluler.
        </p>

        <a class="back" href="{{ url('/') }}">Kembali ke Beranda</a>
    </main>
</body>
</html>
