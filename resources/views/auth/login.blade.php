@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <span class="inline-flex items-center gap-2 rounded-full bg-maroon-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-maroon-700">
        <span class="h-1.5 w-1.5 rounded-full bg-gold-500"></span>
        Autentikasi aman
    </span>
    <h2 class="mt-4 font-display text-2xl font-bold text-slate-900">Selamat datang kembali</h2>
    <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Masukkan kredensial administrator untuk melanjutkan ke panel.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-5">
        @csrf

        <x-form.input name="email" label="Email" type="email" required autofocus autocomplete="username" />

        <div class="space-y-1.5" x-data="passwordField">
            <label for="password" class="block text-sm font-medium text-slate-700">Password <span class="text-maroon-700">*</span></label>
            <div class="relative">
                <input :type="visible ? 'text' : 'password'" name="password" id="password" required autocomplete="current-password"
                    class="form-control block w-full pr-12">
                <button type="button" @click="toggle()" class="absolute inset-y-0 right-0 grid w-11 place-items-center text-slate-400 transition hover:text-maroon-700" :aria-label="visible ? 'Sembunyikan password' : 'Tampilkan password'">
                    <svg x-show="!visible" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z"/><circle cx="12" cy="12" r="2.5"/></svg>
                    <svg x-show="visible" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.7A2 2 0 0013.3 13.4M9.9 5.2A10.8 10.8 0 0112 5c6 0 9.5 7 9.5 7a17.8 17.8 0 01-2.1 3M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 7 9.5 7a9.8 9.8 0 004.1-.9"/></svg>
                </button>
            </div>
            @error('password')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-maroon-700 focus:ring-maroon-600">
            Ingat saya
        </label>

        <button type="submit" class="admin-button admin-button-primary group w-full">
            <span>Masuk ke Panel</span>
            <svg class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-5-5l5 5-5 5"/></svg>
        </button>
    </form>
@endsection
