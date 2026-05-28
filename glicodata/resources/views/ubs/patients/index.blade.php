@extends('layouts.app')

@section('title', 'Pacientes')
@section('protected-navigation', 'true')

@section('content')
    @php
        $patients = [
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43a41001', 'name' => 'Maria Aparecida Santos', 'birth' => '12/03/1958', 'phone' => 'Não informado', 'last' => '24/05/2026'],
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43a41002', 'name' => 'João Alves Ferreira', 'birth' => '08/11/1973', 'phone' => '(42) 99931-4480', 'last' => '22/05/2026'],
            ['id' => '0195e2f1-6b70-7cf0-864d-2f2b43a41003', 'name' => 'Clara Vieira Lima', 'birth' => '19/07/1986', 'phone' => '(42) 99945-1022', 'last' => '18/05/2026'],
        ];
    @endphp

    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Pacientes</p>
                <h1 class="gd-heading">Pacientes da unidade</h1>
                <p class="gd-subtitle">Cadastros vinculados à UBS autenticada.</p>
            </div>
            <span class="gd-demo-note">Exibição demonstrativa</span>
        </div>

        <section class="gd-panel" aria-label="Listagem de pacientes">
            <div class="gd-toolbar">
                <label class="gd-search">
                    <span class="visually-hidden">Buscar paciente</span>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="m17 17-4-4m2-4.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/>
                    </svg>
                    <input class="form-control" type="search" placeholder="Buscar paciente" disabled>
                </label>
                <span class="text-secondary small">3 registros demonstrativos</span>
            </div>

            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Nascimento</th>
                            <th>Telefone</th>
                            <th>Última avaliação</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patients as $patient)
                            <tr>
                                <td data-label="Paciente">
                                    <span class="gd-table-title">{{ $patient['name'] }}</span>
                                    <span class="gd-table-meta">Paciente da UBS</span>
                                </td>
                                <td data-label="Nascimento">{{ $patient['birth'] }}</td>
                                <td data-label="Telefone">{{ $patient['phone'] }}</td>
                                <td data-label="Última avaliação">{{ $patient['last'] }}</td>
                                <td class="text-md-end">
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('ubs.patients.show', $patient['id']) }}">Detalhes</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
