@extends('layouts.public')

@section('title', 'Agenda')
@section('meta_description', 'Jadwal kegiatan dan agenda Forum Pembauran Kebangsaan Kota Malang.')

@section('content')
<x-public-site.page-header
    eyebrow="Jadwal Kegiatan"
    title="Agenda"
    subtitle="Kegiatan mendatang dan yang telah terlaksana oleh FPK Kota Malang." />

<section class="section bg-cream-50 dark:bg-ink-950">
    <div class="container-x max-w-4xl!">
        {{-- Mendatang --}}
        <div class="reveal">
            <h2 class="section-title">Agenda Mendatang</h2>
            <span class="title-rule"></span>
        </div>
        @if ($upcoming->isNotEmpty())
            <div class="mt-8 space-y-4">
                @foreach ($upcoming as $agenda)
                    <div class="reveal" style="--reveal-delay: {{ $loop->index * 60 }}ms"><x-public-site.agenda-card :agenda="$agenda" /></div>
                @endforeach
            </div>
        @else
            <x-public-site.empty-state class="mt-8">Belum ada agenda mendatang yang dijadwalkan.</x-public-site.empty-state>
        @endif

        {{-- Terlaksana --}}
        <div class="reveal mt-16">
            <h2 class="section-title">Agenda Terlaksana</h2>
            <span class="title-rule"></span>
        </div>
        @if ($past->isNotEmpty())
            <div class="mt-8 space-y-4">
                @foreach ($past as $agenda)
                    <div class="reveal" style="--reveal-delay: {{ ($loop->index % 4) * 60 }}ms"><x-public-site.agenda-card :agenda="$agenda" /></div>
                @endforeach
            </div>
            <div class="reveal mt-10">{{ $past->links() }}</div>
        @else
            <x-public-site.empty-state class="mt-8">Belum ada agenda yang terlaksana.</x-public-site.empty-state>
        @endif
    </div>
</section>
@endsection
