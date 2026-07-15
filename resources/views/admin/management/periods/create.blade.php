@extends('layouts.admin')

@section('title', 'Tambah Periode')
@section('heading', 'Tambah Periode Pengurus')

@section('content')
    <form method="POST" action="{{ route('admin.periods.store') }}">
        @csrf
        @include('admin.management.periods._form', ['submitLabel' => 'Simpan Periode'])
    </form>
@endsection
