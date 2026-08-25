@extends('layouts.app')

@section('title', 'Gestão de UBS')

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Administração global</p>
                <h1 class="gd-heading">Unidades de saúde</h1>
                <p class="gd-subtitle">Revise cadastros pendentes, altere o CNES e controle a ativação das unidades.</p>
            </div>
            <div class="d-flex gap-2">
                <a class="btn btn-outline-primary" href="{{ route('admin.dashboard') }}">Voltar</a>
                <a class="btn btn-primary" href="{{ route('ubs.register') }}">Cadastrar UBS</a>
            </div>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        <section class="gd-panel gd-panel-shadow overflow-hidden" aria-labelledby="ubs-list-title">
            <h2 id="ubs-list-title" class="visually-hidden">Lista de unidades</h2>
            <form class="gd-toolbar flex-wrap" method="GET" action="{{ route('admin.ubs.index') }}">
                <div class="d-flex flex-grow-1 gap-2 flex-wrap">
                    <div class="gd-search">
                        <label class="visually-hidden" for="q">Buscar por CNES ou nome</label>
                        <input class="form-control" id="q" name="q" type="search" value="{{ request('q') }}"
                            placeholder="Buscar por CNES ou nome">
                    </div>
                    <label class="visually-hidden" for="status">Filtrar por estado</label>
                    <select class="form-select w-auto" id="status" name="status">
                        <option value="">Todos os estados</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pendentes</option>
                        <option value="active" @selected(request('status') === 'active')>Ativas</option>
                    </select>
                </div>
                <button class="btn btn-outline-primary" type="submit">Filtrar</button>
            </form>

            <div class="table-responsive">
                <table class="table gd-table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">UBS</th>
                            <th scope="col">CNES</th>
                            <th scope="col">Distrito</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="visually-hidden">Ações</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ubsCollection as $ubs)
                            <tr>
                                <td>
                                    <span class="gd-table-title">{{ $ubs->name ?: 'Cadastro sem nome' }}</span>
                                    <span class="gd-table-meta">{{ $ubs->email ?: 'E-mail não informado' }}</span>
                                </td>
                                <td><span class="gd-record-id">{{ $ubs->cnes ?: 'Não definido' }}</span></td>
                                <td>{{ $ubs->district?->name ?: 'Não informado' }}</td>
                                <td>
                                    <span @class([
                                        'gd-status',
                                        'gd-status-success' => $ubs->is_active,
                                        'gd-status-warning' => !$ubs->is_active,
                                    ])>
                                        {{ $ubs->is_active ? 'Ativa' : 'Pendente' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('admin.ubs.edit', $ubs) }}">Gerenciar</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">Nenhuma UBS encontrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">{{ $ubsCollection->links() }}</div>
    </main>
@endsection
