@extends('layouts.admin')

@section('title', 'Ubah Anggota')
@section('heading', 'Ubah Anggota Pengurus')

@section('content')
    <form method="POST" action="{{ route('admin.members.update', $member) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.management.members._form', ['submitLabel' => 'Perbarui Anggota'])
    </form>
@endsection
