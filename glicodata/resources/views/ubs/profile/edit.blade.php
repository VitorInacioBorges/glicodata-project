@extends('layouts.app')

@section('title', 'Perfil da UBS')
@section('protected-navigation', true)

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Conta da unidade</p>
                <h1 class="gd-heading">Perfil da UBS</h1>
                <p class="gd-subtitle">Mantenha os dados institucionais atualizados. CNES e ativação são gerenciados pelo
                    administrador global.</p>
            </div>
            <a class="btn btn-outline-primary" href="{{ route('ubs.lobby') }}">Voltar</a>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">Revise os campos destacados.</div>
        @endif

        <section class="gd-panel gd-panel-shadow p-4">
            <div class="mb-4">
                <span class="text-secondary small d-block">CNES</span>
                <strong class="gd-record-id">{{ $ubs->cnes }}</strong>
            </div>

            <form method="POST" action="{{ route('ubs.profile.update') }}">
                @csrf
                @method('PUT')

                @include('ubs.partials.profile-fields', ['ubs' => $ubs, 'districts' => $districts])

                <button class="btn btn-primary mt-4" type="submit">Salvar perfil</button>
            </form>
        </section>
    </main>
@endsection
