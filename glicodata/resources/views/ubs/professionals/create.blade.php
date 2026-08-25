@extends('layouts.app')

@section('title', 'Nova conta individual')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <p class="gd-eyebrow">Equipe</p>
        <h1 class="gd-heading">Nova conta individual</h1>
        <p class="gd-subtitle">Profissionais exigem CRM ou COREN, UF e especialidade. Gestores não possuem conselho.</p>

        <section class="gd-panel gd-form-panel mt-4">
            <form method="POST" action="{{ route('ubs.professionals.store') }}">
                @csrf
                @include('ubs.professionals._form')
            </form>
        </section>
    </main>
@endsection
