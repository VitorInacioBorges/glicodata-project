@extends('layouts.app')

@section('title', 'Informações da avaliação')
@section('protected-navigation', 'true')

@section('content')
    @php
        $assessments = [
            '0195e2f1-6b70-7cf0-864d-2f2b43c63001' => ['patient' => 'Maria Aparecida Santos', 'patient_id' => '0195e2f1-6b70-7cf0-864d-2f2b43a41001', 'professional' => 'Ana Martins Ribeiro', 'professional_id' => '0195e2f1-6b70-7cf0-864d-2f2b43b52001', 'date' => '24/05/2026', 'risk' => 'Acompanhamento', 'status' => 'warning', 'symptoms' => 'Tontura ocasional e fadiga relatada.'],
            '0195e2f1-6b70-7cf0-864d-2f2b43c63002' => ['patient' => 'João Alves Ferreira', 'patient_id' => '0195e2f1-6b70-7cf0-864d-2f2b43a41002', 'professional' => 'Ana Martins Ribeiro', 'professional_id' => '0195e2f1-6b70-7cf0-864d-2f2b43b52001', 'date' => '22/05/2026', 'risk' => 'Rotina', 'status' => 'success', 'symptoms' => 'Sem sinais de agravamento relatados.'],
            '0195e2f1-6b70-7cf0-864d-2f2b43c63003' => ['patient' => 'Clara Vieira Lima', 'patient_id' => '0195e2f1-6b70-7cf0-864d-2f2b43a41003', 'professional' => 'Carlos de Souza', 'professional_id' => '0195e2f1-6b70-7cf0-864d-2f2b43b52002', 'date' => '18/05/2026', 'risk' => 'Prioridade', 'status' => 'danger', 'symptoms' => 'Necessidade de reavaliação registrada.'],
        ];
        $assessment = $assessments[$id] ?? ['patient' => 'Paciente demonstrativo', 'patient_id' => '0195e2f1-6b70-7cf0-864d-2f2b43a41001', 'professional' => 'Profissional demonstrativo', 'professional_id' => '0195e2f1-6b70-7cf0-864d-2f2b43b52001', 'date' => 'Não informado', 'risk' => 'Não informado', 'status' => 'warning', 'symptoms' => 'Não informado.'];
    @endphp

    <main id="conteudo" class="gd-page">
        <a class="btn btn-outline-primary btn-sm mb-4" href="{{ route('ubs.assessments.index') }}">Voltar para avaliações</a>

        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <p class="gd-eyebrow">Avaliação clínica</p>
                <h1 class="gd-heading">Avaliação de {{ $assessment['patient'] }}</h1>
                <p class="gd-subtitle">Registrada em {{ $assessment['date'] }} por {{ $assessment['professional'] }}.</p>
            </div>
            <span class="gd-demo-note">Exibição demonstrativa</span>
        </div>

        <div class="gd-detail-grid">
            <section class="gd-panel gd-detail-section" aria-labelledby="assessment-data-title">
                <h2 id="assessment-data-title">Resumo do registro</h2>
                <dl class="gd-fields">
                    <div class="gd-field">
                        <dt>Paciente</dt>
                        <dd><a href="{{ route('ubs.patients.show', $assessment['patient_id']) }}">{{ $assessment['patient'] }}</a></dd>
                    </div>
                    <div class="gd-field">
                        <dt>Profissional</dt>
                        <dd><a href="{{ route('ubs.professionals.show', $assessment['professional_id']) }}">{{ $assessment['professional'] }}</a></dd>
                    </div>
                    <div class="gd-field">
                        <dt>Classificação</dt>
                        <dd><span class="gd-status gd-status-{{ $assessment['status'] }}">{{ $assessment['risk'] }}</span></dd>
                    </div>
                    <div class="gd-field">
                        <dt>Data</dt>
                        <dd>{{ $assessment['date'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Sintomas registrados</dt>
                        <dd>{{ $assessment['symptoms'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Identificador</dt>
                        <dd class="gd-record-id">{{ $id }}</dd>
                    </div>
                </dl>
            </section>

            <section class="gd-panel gd-detail-section" aria-labelledby="assessment-actions-title">
                <h2 id="assessment-actions-title">Registro relacionado</h2>
                <ol class="gd-timeline">
                    <li>
                        <strong>Avaliação realizada</strong>
                        <span>{{ $assessment['date'] }} - 09:15</span>
                    </li>
                    <li>
                        <strong>Classificação registrada</strong>
                        <span>{{ $assessment['risk'] }}</span>
                    </li>
                </ol>
            </section>
        </div>
    </main>
@endsection
