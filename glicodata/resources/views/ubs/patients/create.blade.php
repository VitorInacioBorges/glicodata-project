@extends('layouts.app')
@section('title', 'Novo paciente')
@section('protected-navigation', 'true')
@section('content')
    <main id="conteudo" class="gd-page">
        <p class="gd-eyebrow">Pacientes</p>
        <h1 class="gd-heading">Novo paciente</h1>

        <section class="gd-panel gd-form-panel mt-4">
            <form method="POST" action="{{ route('ubs.patients.store') }}">
                @csrf
                @include('ubs.patients._form')
            </form>
        </section>
    </main>
@endsection
