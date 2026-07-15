@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h2 class="font-serif text-lg font-semibold text-slate-800">Masuk ke Panel Admin</h2>
    <p class="mt-1 text-sm text-slate-500">Gunakan email dan password administrator.</p>

    @if ($errors->any())
        <div class="mt-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
        @csrf

        <x-form.input name="email" label="Email" type="email" required autofocus autocomplete="username" />

        <div class="space-y-1">
            <label for="password" class="block text-sm font-medium text-slate-700">Password <span class="text-maroon-700">*</span></label>
            <input type="password" name="password" id="password" required autocomplete="current-password"
                class="block w-full rounded-md border-slate-300 shadow-sm focus:border-maroon-600 focus:ring-maroon-600">
            @error('password')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-maroon-700 focus:ring-maroon-600">
            Ingat saya
        </label>

        <button type="submit" class="w-full rounded-md bg-maroon-700 px-4 py-2.5 font-medium text-cream-50 hover:bg-maroon-800 focus:outline-none focus:ring-2 focus:ring-maroon-600 focus:ring-offset-2">
            Masuk
        </button>
    </form>
@endsection
