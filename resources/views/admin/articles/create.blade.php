@extends('layouts.admin')

@section('title', 'Tambah Artikel')
@section('heading', 'Tambah Artikel')

@section('content')
    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.articles._form', ['submitLabel' => 'Simpan Artikel'])
    </form>
@endsection
