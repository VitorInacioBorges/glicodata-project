@extends('layouts.app')

@section('title', 'Editar profissional')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <p class="gd-eyebrow">Equipe</p>
        <h1 class="gd-heading">Editar {{ $professional->first_name }}</h1>

        <section class="gd-panel gd-form-panel mt-4">
            <form method="POST" action="{{ route('ubs.professionals.update', $professional) }}">
                @csrf
                @method('PUT')
                @include('ubs.professionals._form')
            </form>
        </section>
    </main>
@endsection
