@extends('layouts.admin')

@section('title', 'Ubah Agenda')
@section('heading', 'Ubah Agenda')

@section('content')
    <form method="POST" action="{{ route('admin.agendas.update', $agenda) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('admin.agendas._form', ['submitLabel' => 'Perbarui Agenda'])
    </form>
@endsection
