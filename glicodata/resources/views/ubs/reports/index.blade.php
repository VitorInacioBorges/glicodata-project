@extends('layouts.app')
@section('title', 'Relatórios')
@section('protected-navigation', 'true')
@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Documentação clínica</p>
                <h1 class="gd-heading">Relatórios</h1>
                <p class="gd-subtitle">CRUD assistencial e exportação estatística sem identificadores ou texto livre.</p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary" href="{{ route('ubs.reports.export') }}">
                    Exportar CSV anonimizado
                </a>
                <a class="btn btn-primary" href="{{ route('ubs.reports.create') }}">Novo relatório</a>
            </div>
        </div>

        <div class="alert alert-info">
            A exportação contém somente contagens agregadas. Grupos com menos de cinco registros têm o total suprimido.
        </div>

        <section class="gd-panel">
            <div class="gd-toolbar">
                <strong>{{ $reports->total() }} relatórios</strong>
                <span class="text-secondary small">Dados reais da unidade</span>
            </div>
            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Paciente</th>
                            <th>Risco</th>
                            <th>Atualizado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td data-label="Título">
                                    <span class="gd-table-title">{{ $report->title }}</span>
                                    <span class="gd-table-meta">
                                        {{ $report->assessment?->questionnaireVersion?->questionnaire?->code }}
                                        v{{ $report->assessment?->questionnaireVersion?->version }}
                                    </span>
                                </td>
                                <td data-label="Paciente">{{ $report->assessment?->patient?->name }}</td>
                                <td data-label="Risco">
                                    {{ strtoupper($report->assessment?->risk?->classification?->value ?? '—') }}
                                </td>
                                <td data-label="Atualizado">{{ $report->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="text-md-end">
                                    <a class="btn btn-outline-primary btn-sm"
                                        href="{{ route('ubs.reports.show', $report) }}">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">Nenhum relatório criado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">{{ $reports->links() }}</div>
    </main>
@endsection
