@extends('layouts.app')
@section('title', 'Relatório')
@section('protected-navigation', 'true')
@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex justify-content-between gap-3 mb-4">
            <a class="btn btn-outline-primary btn-sm" href="{{ route('ubs.reports.index') }}">Voltar</a>
            <div class="d-flex gap-2">
                <a class="btn btn-primary btn-sm" href="{{ route('ubs.reports.edit', $report) }}">Editar</a>
                <form method="POST" action="{{ route('ubs.reports.destroy', $report) }}"
                    data-confirm="Remover este relatório?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Remover</button>
                </form>
            </div>
        </div>

        <p class="gd-eyebrow">Relatório clínico</p>
        <h1 class="gd-heading">{{ $report->title }}</h1>
        <p class="gd-subtitle">
            Paciente:
            <a href="{{ route('ubs.patients.show', $report->assessment->patient) }}">
                {{ $report->assessment->patient->name }}
            </a>
            ·
            <a href="{{ route('ubs.assessments.show', $report->assessment) }}">ver anamnese</a>
        </p>

        <div class="gd-detail-grid">
            <section class="gd-panel gd-detail-section">
                <h2>Descrição</h2>
                <div class="gd-preserve-lines">{{ $report->description }}</div>
            </section>
            <section class="gd-panel gd-detail-section">
                <h2>Contexto</h2>
                <dl class="gd-fields">
                    <div class="gd-field">
                        <dt>Profissional</dt>
                        <dd>{{ $report->assessment->user?->name }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Risco</dt>
                        <dd>
                            {{ strtoupper($report->assessment->risk?->classification?->value ?? '—') }} ·
                            {{ $report->assessment->risk?->score }} pontos
                        </dd>
                    </div>
                    <div class="gd-field">
                        <dt>Criado</dt>
                        <dd>{{ $report->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Comentário interno</dt>
                        <dd>{{ $report->comment ?: 'Não informado' }}</dd>
                    </div>
                </dl>
            </section>
        </div>
    </main>
@endsection
