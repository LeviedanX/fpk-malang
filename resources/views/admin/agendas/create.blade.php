@extends('layouts.admin')

@section('title', 'Tambah Agenda')
@section('heading', 'Tambah Agenda')

@section('content')
    <form method="POST" action="{{ route('admin.agendas.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.agendas._form', ['submitLabel' => 'Simpan Agenda'])
    </form>
@endsection
