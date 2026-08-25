@extends('layouts.app')
@section('title', 'Anamnese')
@section('protected-navigation', 'true')
@section('content')
    @php
        $riskClass = match ($assessment->risk?->classification?->value) {
            'low' => 'success',
            'moderate' => 'warning',
            'high' => 'danger',
            default => 'warning',
        };
    @endphp
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
            <a class="btn btn-outline-primary btn-sm align-self-start"
                href="{{ route('ubs.assessments.index') }}">Voltar</a>
            <div class="d-flex gap-2">
                @if ($assessment->status->value === 'draft')
                    <a class="btn btn-primary btn-sm" href="{{ route('ubs.assessments.edit', $assessment) }}">Continuar
                        preenchimento</a>
                @endif
                <form method="POST" action="{{ route('ubs.assessments.destroy', $assessment) }}"
                    data-confirm="Remover esta anamnese?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm">Remover</button>
                </form>
            </div>
        </div>

        <p class="gd-eyebrow">Anamnese {{ $assessment->status->value === 'completed' ? 'concluída' : 'em rascunho' }}</p>
        <h1 class="gd-heading">{{ $assessment->patient?->first_name }}</h1>
        <p class="gd-subtitle">Responsável: {{ $assessment->professional?->first_name }}
            ({{ $assessment->professional?->specialty }}) · iniciada em
            {{ $assessment->started_at?->format('d/m/Y H:i') ?? $assessment->created_at->format('d/m/Y H:i') }}</p>
        <div class="gd-detail-grid">
            <section class="gd-panel gd-detail-section">
                <h2>Resultado e contexto</h2>
                <dl class="gd-fields">
                    <div class="gd-field">
                        <dt>Status</dt>
                        <dd>{{ $assessment->status->value === 'completed' ? 'Concluída' : 'Rascunho' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Questionário</dt>
                        <dd>{{ $assessment->questionnaireVersion?->questionnaire?->title ?? 'Registro legado' }} ·
                            v{{ $assessment->questionnaireVersion?->version ?? '-' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Conclusão</dt>
                        <dd>{{ $assessment->completed_at?->format('d/m/Y H:i') ?? 'Não concluída' }}</dd>
                    </div>
                    @if ($assessment->risk)
                        <div class="gd-field">
                            <dt>Classificação de risco</dt>
                            <dd>
                                <span class="gd-status gd-status-{{ $riskClass }}">
                                    {{ strtoupper($assessment->risk->classification->value) }}
                                </span>
                            </dd>
                        </div>
                        <div class="gd-field">
                            <dt>Pontuação / probabilidade</dt>
                            <dd>{{ $assessment->risk->score }} pontos ·
                                {{ number_format($assessment->risk->percentage, 0, ',', '.') }}%</dd>
                        </div>
                    @endif
                </dl>
            </section>
            <section class="gd-panel gd-detail-section">
                <h2>Relatório</h2>
                @if ($assessment->report)
                    <p class="fw-semibold">Relatório da anamnese</p>
                    <a class="btn btn-outline-primary btn-sm"
                        href="{{ route('ubs.reports.show', $assessment->report) }}">Abrir relatório</a>
                @elseif ($assessment->status->value === 'completed')
                    <p class="text-secondary">Ainda não há relatório para esta conclusão.</p>
                    <a class="btn btn-primary btn-sm"
                        href="{{ route('ubs.reports.create', ['assessment_id' => $assessment->id]) }}">Criar relatório</a>
                @else
                    <p class="text-secondary">Conclua a anamnese para gerar um relatório.</p>
                @endif
            </section>
        </div>
        @if ($assessment->questionnaireVersion)
            <section class="gd-panel gd-detail-section mt-3">
                <h2>Respostas registradas</h2>
                <dl class="gd-fields">
                    @foreach ($assessment->questionnaireVersion->schema as $question)
                        @php
                            $value = $assessment->answers[$question['code']] ?? null;
                            if (($question['type'] ?? '') === 'choice') {
                                $value = collect($question['options'])->firstWhere('value', $value)['label'] ?? $value;
                            }
                        @endphp
                        <div class="gd-field">
                            <dt>{{ $question['label'] }}</dt>
                            <dd>{{ $value ?? 'Não respondida' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif
    </main>
@endsection
