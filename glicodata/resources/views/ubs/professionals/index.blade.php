@extends('layouts.app')

@section('title', 'Profissionais')
@section('protected-navigation', 'true')

@section('content')
    @php
        $professionals = [
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43b52001', 'name' => 'Ana Martins Ribeiro', 'role' => 'Profissional', 'area' => 'Medicina geral', 'email' => 'ana.martins@ubs.demo'],
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43b52002', 'name' => 'Carlos de Souza', 'role' => 'Profissional', 'area' => 'Enfermagem', 'email' => 'carlos.souza@ubs.demo'],
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43b52003', 'name' => 'Lúcia Almeida', 'role' => 'Administrador', 'area' => 'Gestão da unidade', 'email' => 'lucia.almeida@ubs.demo'],
        ];
    @endphp

    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Perfis da unidade</p>
                <h1 class="gd-heading">Profissionais</h1>
                <p class="gd-subtitle">Equipe e perfis administrativos vinculados à UBS.</p>
            </div>
            <span class="gd-demo-note">Exibição demonstrativa</span>
        </div>

        <section class="gd-panel" aria-label="Listagem de profissionais">
            <div class="gd-toolbar">
                <label class="gd-search">
                    <span class="visually-hidden">Buscar profissional</span>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m17 17-4-4m2-4.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>
                    </svg>
                    <input class="form-control" type="search" placeholder="Buscar profissional" disabled>
                </label>
                <span class="text-secondary small">3 perfis demonstrativos</span>
            </div>

            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Profissional</th>
                            <th>Perfil</th>
                            <th>Área</th>
                            <th>E-mail</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($professionals as $professional)
                            <tr>
                                <td data-label="Profissional">
                                    <span class="gd-table-title">{{ $professional['name'] }}</span>
                                    <span class="gd-table-meta">Vinculado à UBS</span>
                                </td>
                                <td data-label="Perfil">{{ $professional['role'] }}</td>
                                <td data-label="Área">{{ $professional['area'] }}</td>
                                <td data-label="E-mail">{{ $professional['email'] }}</td>
                                <td class="text-md-end">
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('ubs.professionals.show', $professional['id']) }}">Detalhes</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
