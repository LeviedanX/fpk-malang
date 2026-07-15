@extends('layouts.admin')

@section('title', 'Ubah Artikel')
@section('heading', 'Ubah Artikel')

@section('content')
    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.articles._form', ['submitLabel' => 'Perbarui Artikel'])
    </form>
@endsection
