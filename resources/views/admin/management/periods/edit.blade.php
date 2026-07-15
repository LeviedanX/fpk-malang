@extends('layouts.admin')

@section('title', 'Ubah Periode')
@section('heading', 'Ubah Periode Pengurus')

@section('content')
    <form method="POST" action="{{ route('admin.periods.update', $period) }}">
        @csrf
        @method('PUT')
        @include('admin.management.periods._form', ['submitLabel' => 'Perbarui Periode'])
    </form>
@endsection
