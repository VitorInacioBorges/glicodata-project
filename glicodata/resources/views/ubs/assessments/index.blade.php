@extends('layouts.app')
@section('title', 'Anamneses')
@section('protected-navigation', 'true')
@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Avaliação clínica</p>
                <h1 class="gd-heading">Anamneses</h1>
            </div>
            <a class="btn btn-primary" href="{{ route('ubs.assessments.create') }}">Nova anamnese</a>
        </div>

        <section class="gd-panel">
            <div class="gd-toolbar">
                <strong>{{ $assessments->total() }} registros</strong>
                <span class="text-secondary small">Rascunhos e conclusões</span>
            </div>
            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Profissional</th>
                            <th>Questionário</th>
                            <th>Status</th>
                            <th>Risco</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assessments as $assessment)
                            @php
                                $riskClass = match ($assessment->risk?->classification?->value) {
                                    'low' => 'success',
                                    'moderate' => 'warning',
                                    'high' => 'danger',
                                    default => 'warning',
                                };
                            @endphp
                            <tr>
                                <td data-label="Paciente">
                                    <span class="gd-table-title">{{ $assessment->patient?->first_name }}</span>
                                    <span class="gd-table-meta">{{ $assessment->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td data-label="Profissional">
                                    {{ $assessment->professional?->first_name }} · {{ $assessment->professional?->specialty }}
                                </td>
                                <td data-label="Questionário">
                                    {{ $assessment->questionnaireVersion?->questionnaire?->code ?? 'Legado' }}
                                    v{{ $assessment->questionnaireVersion?->version ?? '-' }}
                                </td>
                                <td data-label="Status">
                                    <span class="gd-status gd-status-{{ $assessment->status->value === 'completed' ? 'success' : 'warning' }}">
                                        {{ $assessment->status->value === 'completed' ? 'Concluída' : 'Rascunho' }}
                                    </span>
                                </td>
                                <td data-label="Risco">
                                    @if ($assessment->risk)
                                        <span class="gd-status gd-status-{{ $riskClass }}">
                                            {{ strtoupper($assessment->risk->classification->value) }} ·
                                            {{ $assessment->risk->score }} pts
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-md-end">
                                    <a
                                        class="btn btn-outline-primary btn-sm"
                                        href="{{ $assessment->status->value === 'draft' ? route('ubs.assessments.edit', $assessment) : route('ubs.assessments.show', $assessment) }}"
                                    >{{ $assessment->status->value === 'draft' ? 'Preencher' : 'Detalhes' }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">Nenhuma anamnese registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">{{ $assessments->links() }}</div>
    </main>
@endsection
