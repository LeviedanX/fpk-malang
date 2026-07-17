@extends('layouts.admin')

@section('title', 'Akun Admin')
@section('heading', 'Akun Admin')

@section('content')
    @php($user = auth()->user())

    <x-admin.card title="Profil Akun" description="Ubah nama dan email administrator.">
        <form method="POST" action="{{ route('admin.account.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form.input name="name" label="Nama" :value="$user->name" required />
            <x-form.input name="email" label="Email" type="email" :value="$user->email" required />
            <div class="flex justify-stretch sm:justify-end">
                <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Simpan Profil</button>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card title="Ubah Password" description="Gunakan password yang kuat dan tidak dipakai di tempat lain.">
        <form method="POST" action="{{ route('admin.account.password') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form.input name="current_password" label="Password Saat Ini" type="password" required autocomplete="current-password" />
            <x-form.input name="password" label="Password Baru" type="password" required autocomplete="new-password" />
            <x-form.input name="password_confirmation" label="Konfirmasi Password Baru" type="password" required autocomplete="new-password" />
            <div class="flex justify-stretch sm:justify-end">
                <button type="submit" class="admin-button admin-button-primary w-full sm:w-auto">Perbarui Password</button>
            </div>
        </form>
    </x-admin.card>
@endsection
