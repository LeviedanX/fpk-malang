@extends('layouts.admin')

@section('title', 'Tambah Anggota')
@section('heading', 'Tambah Anggota Pengurus')

@section('content')
    @if ($periods->isEmpty())
        <x-admin.card>
            <p class="text-sm text-slate-600">Buat periode terlebih dahulu sebelum menambahkan anggota.
                <a href="{{ route('admin.periods.create') }}" class="text-maroon-700 hover:underline">Tambah periode</a>.</p>
        </x-admin.card>
    @else
        <form method="POST" action="{{ route('admin.members.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.management.members._form', ['submitLabel' => 'Simpan Anggota'])
        </form>
    @endif
@endsection
