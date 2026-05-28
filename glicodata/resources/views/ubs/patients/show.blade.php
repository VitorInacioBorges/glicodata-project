@extends('layouts.app')

@section('title', 'Informações do paciente')
@section('protected-navigation', 'true')

@section('content')
    @php
        $patients = [
            '0195e2f1-6b70-7cf0-864d-2f2b43a41001' => ['name' => 'Maria Aparecida Santos', 'birth' => '12/03/1958', 'cpf' => '***.***.***-41', 'phone' => 'Não informado', 'address' => 'Não informado', 'date' => '24/05/2026', 'professional' => 'Ana Martins Ribeiro'],
            '0195e2f1-6b70-7cf0-864d-2f2b43a41002' => ['name' => 'João Alves Ferreira', 'birth' => '08/11/1973', 'cpf' => '***.***.***-92', 'phone' => '(42) 99931-4480', 'address' => 'Rua das Acácias, 72', 'date' => '22/05/2026', 'professional' => 'Ana Martins Ribeiro'],
            '0195e2f1-6b70-7cf0-864d-2f2b43a41003' => ['name' => 'Clara Vieira Lima', 'birth' => '19/07/1986', 'cpf' => '***.***.***-08', 'phone' => '(42) 99945-1022', 'address' => 'Vila Esperança', 'date' => '18/05/2026', 'professional' => 'Carlos de Souza'],
        ];
        $patient = $patients[$id] ?? ['name' => 'Paciente demonstrativo', 'birth' => 'Não informado', 'cpf' => 'Não informado', 'phone' => 'Não informado', 'address' => 'Não informado', 'date' => 'Não informado', 'professional' => 'Não informado'];
    @endphp

    <main id="conteudo" class="gd-page">
        <a class="btn btn-outline-primary btn-sm mb-4" href="{{ route('ubs.patients.index') }}">Voltar para pacientes</a>

        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <p class="gd-eyebrow">Paciente</p>
                <h1 class="gd-heading">{{ $patient['name'] }}</h1>
                <p class="gd-subtitle">Registro assistencial da unidade autenticada.</p>
            </div>
            <span class="gd-demo-note">Exibição demonstrativa</span>
        </div>

        <div class="gd-detail-grid">
            <section class="gd-panel gd-detail-section" aria-labelledby="patient-data-title">
                <h2 id="patient-data-title">Informações cadastrais</h2>
                <dl class="gd-fields">
                    <div class="gd-field">
                        <dt>Data de nascimento</dt>
                        <dd>{{ $patient['birth'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>CPF</dt>
                        <dd>{{ $patient['cpf'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Telefone</dt>
                        <dd>{{ $patient['phone'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Endereço</dt>
                        <dd>{{ $patient['address'] }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Identificador</dt>
                        <dd class="gd-record-id">{{ $id }}</dd>
                    </div>
                </dl>
            </section>

            <section class="gd-panel gd-detail-section" aria-labelledby="patient-history-title">
                <h2 id="patient-history-title">Atendimentos recentes</h2>
                <ol class="gd-timeline">
                    <li>
                        <strong>Avaliação registrada</strong>
                        <span>{{ $patient['date'] }} - {{ $patient['professional'] }}</span>
                    </li>
                    <li>
                        <strong>Cadastro revisado</strong>
                        <span>02/05/2026 - Unidade</span>
                    </li>
                </ol>
            </section>
        </div>
    </main>
@endsection
