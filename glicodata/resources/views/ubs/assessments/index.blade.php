@extends('layouts.app')

@section('title', 'Avaliações recentes')
@section('protected-navigation', 'true')

@section('content')
    @php
        $assessments = [
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43c63001', 'patient' => 'Maria Aparecida Santos', 'professional' => 'Ana Martins Ribeiro', 'date' => '24/05/2026', 'risk' => 'Acompanhamento', 'status' => 'warning'],
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43c63002', 'patient' => 'João Alves Ferreira', 'professional' => 'Ana Martins Ribeiro', 'date' => '22/05/2026', 'risk' => 'Rotina', 'status' => 'success'],
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43c63003', 'patient' => 'Clara Vieira Lima', 'professional' => 'Carlos de Souza', 'date' => '18/05/2026', 'risk' => 'Prioridade', 'status' => 'danger'],
        ];
    @endphp

    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Avaliações</p>
                <h1 class="gd-heading">Avaliações mais recentes</h1>
                <p class="gd-subtitle">Registros associados a pacientes e profissionais da unidade.</p>
            </div>
            <span class="gd-demo-note">Exibição demonstrativa</span>
        </div>

        <section class="gd-panel" aria-label="Listagem de avaliações recentes">
            <div class="gd-toolbar">
                <label class="gd-search">
                    <span class="visually-hidden">Buscar avaliação</span>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m17 17-4-4m2-4.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>
                    </svg>
                    <input class="form-control" type="search" placeholder="Buscar avaliação" disabled>
                </label>
                <span class="text-secondary small">3 avaliações demonstrativas</span>
            </div>

            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Profissional</th>
                            <th>Data</th>
                            <th>Classificação</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($assessments as $assessment)
                            <tr>
                                <td data-label="Paciente">
                                    <span class="gd-table-title">{{ $assessment['patient'] }}</span>
                                </td>
                                <td data-label="Profissional">{{ $assessment['professional'] }}</td>
                                <td data-label="Data">{{ $assessment['date'] }}</td>
                                <td data-label="Classificação">
                                    <span class="gd-status gd-status-{{ $assessment['status'] }}">{{ $assessment['risk'] }}</span>
                                </td>
                                <td class="text-md-end">
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('ubs.assessments.show', $assessment['id']) }}">Detalhes</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
