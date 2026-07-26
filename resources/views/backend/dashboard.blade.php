@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1 class="h3">Ciao, {{ auth()->user()->name }} {{ auth()->user()->surname }}</h1>
    <p class="text-muted">Sezione contenuti in arrivo.</p>
@endsection
