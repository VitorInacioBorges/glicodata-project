@extends('layouts.app')

@section('title', 'Informações do profissional')
@section('protected-navigation', 'true')

@section('content')
    @php
        $professionals = [
            '0195e2f1-6b70-7cf0-864d-2f2b43b52001' => ['name' => 'Ana Martins Ribeiro', 'role' => 'Profissional', 'area' => 'Medicina geral', 'email' => 'ana.martins@ubs.demo', 'phone' => 'Não informado', 'patient' => 'Maria Aparecida Santos'],
            '0195e2f1-6b70-7cf0-864d-2f2b43b52002' => ['name' => 'Carlos de Souza', 'role' => 'Profissional', 'area' => 'Enfermagem', 'email' => 'carlos.souza@ubs.demo', 'phone' => '(42) 99942-2210', 'patient' => 'Clara Vieira Lima'],
            '0195e2f1-6b70-7cf0-864d-2f2b43b52003' => ['name' => 'Lúcia Almeida', 'role' => 'Administrador', 'area' => 'Gestão da unidade', 'email' => 'lucia.almeida@ubs.demo', 'phone' => '(42) 3901-1700', 'patient' => 'Não aplicável'],
        ];
        $professional = $professionals[$id] ?? ['name' => 'Profissional demonstrativo', 'role' => 'Profissional', 'area' => 'Não informado', 'email' => 'Não informado', 'phone' => 'Não informado', 'patient' => 'Não informado'];
    @endphp

    <main id="conteudo" class="gd-page">
        <a class="btn btn-outline-primary btn-sm mb-4" href="{{ route('ubs.professionals.index') }}">Voltar para profissionais</a>

        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <p class="gd-eyebrow">Profissional</p>
                <h1 class="gd-heading">{{ $professional['name'] }}</h1>
                <p class="gd-subtitle">Perfil vinculado à unidade autenticada.</p>
            </div>
            <span class="gd-demo-note">Exibição demonstrativa</span>
        </div>

        <div class="gd-detail-grid">
            <section class="gd-panel gd-detail-section" aria-labelledby="professional-data-title">
                <h2 id="professional-data-title">Informações do perfil</h2>
                <dl class="gd-fields">
                    <div class="gd-field">
                        <dt>Perfil de acesso</dt>
                        <dd><span class="gd-status gd-status-success">{{ $professional['role'] }}</span></dd>
                    </div>
                    <div class="gd-field">
                        <dt>Área</dt>
                        <dd>{{ $professional['area'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>E-mail</dt>
                        <dd>{{ $professional['email'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Telefone</dt>
                        <dd>{{ $professional['phone'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Endereço</dt>
                        <dd>Não informado</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Identificador</dt>
                        <dd class="gd-record-id">{{ $id }}</dd>
                    </div>
                </dl>
            </section>

            <section class="gd-panel gd-detail-section" aria-labelledby="professional-history-title">
                <h2 id="professional-history-title">Atividade recente</h2>
                <ol class="gd-timeline">
                    <li>
                        <strong>Avaliação registrada</strong>
                        <span>24/05/2026 - {{ $professional['patient'] }}</span>
                    </li>
                    <li>
                        <strong>Avaliação registrada</strong>
                        <span>22/05/2026 - João Alves Ferreira</span>
                    </li>
                </ol>
            </section>
        </div>
    </main>
@endsection
