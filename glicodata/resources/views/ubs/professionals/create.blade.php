@extends('layouts.app')

@section('title', 'Novo profissional')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <p class="gd-eyebrow">Equipe</p>
        <h1 class="gd-heading">Novo profissional</h1>
        <p class="gd-subtitle">Cadastre somente o primeiro nome e a especialidade.</p>

        <section class="gd-panel gd-form-panel mt-4">
            <form method="POST" action="{{ route('ubs.professionals.store') }}">
                @csrf
                @include('ubs.professionals._form')
            </form>
        </section>
    </main>
@endsection
