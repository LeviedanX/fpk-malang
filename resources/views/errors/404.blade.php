@extends('layouts.public')

@section('title', 'Halaman tidak ditemukan')

@section('content')
    <section class="relative isolate flex min-h-[70vh] items-center overflow-hidden bg-maroon-950 text-cream-50">
        <div class="hero-motif parallax-layer pointer-events-none absolute inset-0 -z-10 opacity-40" data-parallax="0.018" aria-hidden="true"></div>
        <div class="hero-glow parallax-layer pointer-events-none absolute inset-0 -z-10" data-parallax="0.035" aria-hidden="true"></div>
        <div class="container-x flex flex-col items-center py-24 text-center">
            <p class="reveal reveal-scale font-display text-7xl font-extrabold text-gold-400 sm:text-8xl">404</p>
            <h1 class="reveal mt-4 font-display text-2xl font-bold sm:text-3xl" style="--reveal-delay: 80ms">Halaman tidak ditemukan</h1>
            <p class="reveal mt-3 max-w-md text-cream-100/80" style="--reveal-delay: 140ms">Maaf, halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
            <div class="reveal mt-8 flex flex-wrap justify-center gap-3" style="--reveal-delay: 200ms">
                <a href="{{ route('home') }}" class="btn-gold">Kembali ke Beranda</a>
                <a href="{{ route('articles.index') }}" class="btn-ghost-light">Lihat Artikel</a>
            </div>
        </div>
    </section>
@endsection
