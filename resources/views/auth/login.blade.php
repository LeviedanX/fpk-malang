@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="mt-3 text-center">
        <h2 class="font-display text-[1.4rem] font-bold leading-tight text-maroon-800">Autentikasi Administrator</h2>
        <span class="mx-auto mt-2.5 block h-1 w-10 rounded-full bg-gold-500"></span>
    </div>

    @if ($errors->any())
        <div class="mt-6 flex items-start gap-2.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf

        <x-form.input name="email" label="Email" type="email" required autofocus autocomplete="username" />

        <div class="space-y-1.5" x-data="passwordField">
            <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi <span class="text-maroon-700">*</span></label>
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

        <button type="submit" class="admin-button admin-button-primary mt-1 w-full">
            <span>Masuk</span>
        </button>
    </form>
@endsection
