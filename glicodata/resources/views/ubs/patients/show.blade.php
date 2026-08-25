@extends('layouts.app')

@section('title', 'Paciente')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex justify-content-between gap-3 mb-4">
            <a class="btn btn-outline-primary btn-sm" href="{{ route('ubs.patients.index') }}">Voltar</a>
            <div class="d-flex gap-2">
                <a class="btn btn-primary btn-sm" href="{{ route('ubs.patients.edit', $patient) }}">Editar</a>
                <form method="POST" action="{{ route('ubs.patients.destroy', $patient) }}"
                    data-confirm="Remover este paciente?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">Remover</button>
                </form>
            </div>
        </div>

        <p class="gd-eyebrow">Paciente</p>
        <h1 class="gd-heading">{{ $patient->first_name }}</h1>
        <p class="gd-subtitle">Registro assistencial da unidade autenticada.</p>

        <div class="gd-detail-grid">
            <section class="gd-panel gd-detail-section" aria-labelledby="patient-data-title">
                <h2 id="patient-data-title">Informações cadastrais</h2>
                <dl class="gd-fields">
                    <div class="gd-field">
                        <dt>Sexo</dt>
                        <dd>{{ $patient->sex ? 'Masculino' : 'Feminino' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Bairro</dt>
                        <dd>{{ $patient->neighborhood }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Logradouro</dt>
                        <dd>{{ $patient->street_name ?: 'Não informado' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Identificador</dt>
                        <dd class="gd-record-id">{{ $patient->id }}</dd>
                    </div>
                </dl>
            </section>
            <section class="gd-panel gd-detail-section" aria-labelledby="patient-history-title">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 id="patient-history-title" class="mb-0">Anamneses</h2>
                    <a class="btn btn-outline-primary btn-sm"
                        href="{{ route('ubs.assessments.create', ['patient_id' => $patient->id]) }}">Iniciar</a>
                </div>
                <ol class="gd-timeline">
                    @forelse ($patient->assessments->sortByDesc('created_at')->take(8) as $assessment)
                        <li>
                            <strong>
                                <a href="{{ route('ubs.assessments.show', $assessment) }}">
                                    {{ $assessment->status->value === 'completed' ? 'Concluída' : 'Rascunho' }}
                                </a>
                            </strong>
                            <span>
                                {{ $assessment->created_at->format('d/m/Y H:i') }} ·
                                {{ $assessment->professional?->first_name }} ({{ $assessment->professional?->specialty }})
                            </span>
                        </li>
                    @empty
                        <li>
                            <strong>Nenhuma anamnese</strong>
                            <span>Inicie o primeiro preenchimento.</span>
                        </li>
                    @endforelse
                </ol>
            </section>
        </div>
    </main>
@endsection
