@extends('layouts.app')

@section('title', 'Profissionais')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Equipe da unidade</p>
                <h1 class="gd-heading">Profissionais</h1>
            </div>
            <a class="btn btn-primary" href="{{ route('ubs.professionals.create') }}">Novo profissional</a>
        </div>

        <section class="gd-panel" aria-label="Listagem de profissionais">
            <div class="gd-toolbar">
                <strong>{{ $professionals->total() }} profissionais</strong>
            </div>
            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Especialidade</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($professionals as $professional)
                            <tr>
                                <td data-label="Nome">
                                    <span class="gd-table-title">{{ $professional->first_name }}</span>
                                </td>
                                <td data-label="Especialidade">{{ $professional->specialty }}</td>
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
                                <td colspan="4" class="text-center text-secondary py-5">
                                    Nenhum profissional cadastrado.
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
