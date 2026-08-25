@extends('layouts.app')

@section('title', 'Profissionais')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Identidades da unidade</p>
                <h1 class="gd-heading">Profissionais e gestores</h1>
                <p class="gd-subtitle">Contas individuais com papel e vínculo profissional verificável.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('ubs.professionals.create') }}">Nova conta</a>
        </div>

        <section class="gd-panel" aria-label="Listagem de profissionais">
            <div class="gd-toolbar">
                <strong>{{ $professionals->total() }} contas</strong>
                <span class="text-secondary small">Escopo da UBS autenticada</span>
            </div>
            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Papel</th>
                            <th>Conselho</th>
                            <th>Especialidade</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($professionals as $professional)
                            <tr>
                                <td data-label="Nome">
                                    <span class="gd-table-title">{{ $professional->name }}</span>
                                    <span class="gd-table-meta">{{ $professional->email }}</span>
                                </td>
                                <td data-label="Papel">
                                    {{ $professional->role->value === 'admin' ? 'Gestor da UBS' : 'Profissional' }}
                                </td>
                                <td data-label="Conselho">
                                    {{ $professional->council_type?->value ? $professional->council_type->value . ' ' . $professional->council_number . '/' . $professional->council_uf : 'Não aplicável' }}
                                </td>
                                <td data-label="Especialidade">{{ $professional->specialty ?: 'Não aplicável' }}</td>
                                <td data-label="Status">
                                    <span class="gd-status gd-status-{{ $professional->is_active ? 'success' : 'danger' }}">
                                        {{ $professional->is_active ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="text-md-end">
                                    <a class="btn btn-outline-primary btn-sm"
                                        href="{{ route('ubs.professionals.show', $professional) }}">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">
                                    Nenhuma conta individual cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">{{ $professionals->links() }}</div>
    </main>
@endsection
