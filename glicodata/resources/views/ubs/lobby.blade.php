@extends('layouts.app')

@section('title', 'Painel')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <section class="gd-hero" aria-labelledby="lobby-title">
            <div>
                <h1 id="lobby-title">Sistema GlicoData</h1>
                <p>Gerencie pacientes e profissionais para anamneses de Diabetes Mellitus Tipo II</p>
            </div>
        </section>

        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Seções</p>
                <h2 class="gd-heading mb-0">{{ auth('ubs')->user()?->name ?? 'Unidade autenticada' }}</h2>
            </div>
        </div>

        <section class="gd-module-grid" aria-label="Módulos do GlicoData">
            <a class="gd-module" href="{{ route('ubs.patients.index') }}">
                <img src="{{ asset('images/module-patients.svg') }}" alt="">
                <h2>Pacientes</h2>
                <p>Pacientes cadastrados e vinculados à UBS. Acompanhe seus dados registrados.</p>
                <span class="gd-module-action">Abrir listagem</span>
            </a>

            <a class="gd-module" href="{{ route('ubs.professionals.index') }}">
                <img src="{{ asset('images/module-professionals.svg') }}" alt="">
                <h2>Profissionais</h2>
                <p>Identidades assistenciais responsáveis pelas anamneses e relatórios.</p>
                <span class="gd-module-action">Abrir listagem</span>
            </a>

            <a class="gd-module" href="{{ route('ubs.assessments.index') }}">
                <img src="{{ asset('images/module-assessments.svg') }}" alt="">
                <h2>Avaliações</h2>
                <p>Registros clínicos vinculados ao paciente e a um profissional.</p>
                <span class="gd-module-action">Abrir listagem</span>
            </a>
        </section>
    </main>
@endsection
