@extends('layouts.app')

@section('title', 'Pacientes')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Cadastro assistencial</p>
                <h1 class="gd-heading">Pacientes</h1>
            </div>
            <a class="btn btn-primary" href="{{ route('ubs.patients.create') }}">Novo paciente</a>
        </div>

        <section class="gd-panel" aria-label="Listagem de pacientes">
            <div class="gd-toolbar">
                <strong>{{ $patients->total() }} {{ $patients->total() === 1 ? 'paciente' : 'pacientes' }}</strong>
            </div>
            <div class="table-responsive">
                <table class="table gd-table gd-responsive-table align-middle">
                    <thead>
                        <tr>
                            <th>Paciente</th>
                            <th>Bairro</th>
                            <th>Logradouro</th>
                            <th>Anamneses</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($patients as $patient)
                            <tr>
                                <td data-label="Paciente">
                                    <span class="gd-table-title">{{ $patient->first_name }}</span>
                                </td>
                                <td data-label="Bairro">{{ $patient->neighborhood }}</td>
                                <td data-label="Logradouro">{{ $patient->street_name ?: 'Não informado' }}</td>
                                <td data-label="Anamneses">{{ $patient->assessments_count }}</td>
                                <td class="text-md-end">
                                    <a class="btn btn-outline-primary btn-sm"
                                        href="{{ route('ubs.patients.show', $patient) }}">Detalhes</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-5">
                                    Nenhum paciente cadastrado nesta UBS.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-4">{{ $patients->links() }}</div>
    </main>
@endsection
