@extends('layouts.app')

@section('title', 'Painel')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <section class="gd-hero" aria-labelledby="lobby-title">
            <div>
                <p class="gd-eyebrow text-white-50">Unidade Básica de Saúde</p>
                <h1 id="lobby-title">Atendimento da unidade</h1>
                <p>Área de trabalho para acompanhamento dos registros assistenciais da UBS autenticada.</p>
            </div>
            <div class="gd-hero-unit">
                <small>Sessão ativa</small>
                <strong>{{ auth('ubs')->user()?->name ?? 'Unidade autenticada' }}</strong>
                <div class="small text-white-50 mt-1">Acesso institucional Keycloak</div>
            </div>
        </section>

        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Módulos</p>
                <h2 class="gd-heading mb-0">Visão da unidade</h2>
            </div>
            <span class="gd-demo-note">Dados demonstrativos para validação visual</span>
        </div>

        <section class="gd-module-grid" aria-label="Módulos do GlicoData">
            <a class="gd-module" href="{{ route('ubs.patients.index') }}">
                <img src="{{ asset('images/module-patients.svg') }}" alt="">
                <h2>Pacientes</h2>
                <p>Cadastros vinculados à UBS e acompanhamento dos dados disponíveis.</p>
                <span class="gd-module-action">Abrir listagem</span>
            </a>

            <a class="gd-module" href="{{ route('ubs.professionals.index') }}">
                <img src="{{ asset('images/module-professionals.svg') }}" alt="">
                <h2>Profissionais</h2>
                <p>Equipe assistencial e administração associadas à unidade.</p>
                <span class="gd-module-action">Abrir listagem</span>
            </a>

            <a class="gd-module" href="{{ route('ubs.assessments.index') }}">
                <img src="{{ asset('images/module-assessments.svg') }}" alt="">
                <h2>Avaliações recentes</h2>
                <p>Registros clínicos vinculados ao paciente e ao profissional executor.</p>
                <span class="gd-module-action">Abrir listagem</span>
            </a>
        </section>
    </main>
@endsection
