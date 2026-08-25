@extends('layouts.app')
@section('title', 'Conta individual')
@section('protected-navigation', 'true')
@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex justify-content-between gap-3 mb-4">
            <a class="btn btn-outline-primary btn-sm" href="{{ route('ubs.professionals.index') }}">Voltar</a>
            <div class="d-flex gap-2">
                <a class="btn btn-primary btn-sm" href="{{ route('ubs.professionals.edit', $professional) }}">Editar</a>
                <form method="POST" action="{{ route('ubs.professionals.destroy', $professional) }}"
                    data-confirm="Remover esta conta?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" type="submit">Remover</button>
                </form>
            </div>
        </div>

        <p class="gd-eyebrow">Identidade individual</p>
        <h1 class="gd-heading">{{ $professional->name }}</h1>
        <p class="gd-subtitle">{{ $professional->email }}</p>
        <div class="gd-detail-grid">
            <section class="gd-panel gd-detail-section">
                <h2>Conta e vínculo</h2>
                <dl class="gd-fields">
                    <div class="gd-field">
                        <dt>Papel</dt>
                        <dd>{{ $professional->role->value === 'admin' ? 'Gestor da UBS' : 'Profissional de saúde' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Status</dt>
                        <dd>
                            <span class="gd-status gd-status-{{ $professional->is_active ? 'success' : 'danger' }}">
                                {{ $professional->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </dd>
                    </div>
                    <div class="gd-field">
                        <dt>Conselho</dt>
                        <dd>{{ $professional->council_type?->value ?: 'Não aplicável' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Registro / UF</dt>
                        <dd>{{ $professional->council_number ? $professional->council_number . '/' . $professional->council_uf : 'Não aplicável' }}
                        </dd>
                    </div>
                    <div class="gd-field">
                        <dt>Especialidade</dt>
                        <dd>{{ $professional->specialty ?: 'Não aplicável' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>CPF</dt>
                        <dd>{{ $professional->cpf }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Nascimento</dt>
                        <dd>{{ $professional->birth->format('d/m/Y') }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Telefone</dt>
                        <dd>{{ $professional->phone ?: 'Não informado' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Endereço</dt>
                        <dd>{{ $professional->address ?: 'Não informado' }}</dd>
                    </div>
                    <div class="gd-field">
                        <dt>Identificador</dt>
                        <dd class="gd-record-id">{{ $professional->id }}</dd>
                    </div>
                </dl>
            </section>
            <section class="gd-panel gd-detail-section">
                <h2>Atividade clínica</h2>
                <ol class="gd-timeline">
                    @forelse ($professional->assessments->sortByDesc('created_at')->take(8) as $assessment)
                        <li>
                            <strong>{{ $assessment->patient?->name }}</strong>
                            <span>
                                {{ $assessment->created_at->format('d/m/Y H:i') }} ·
                                {{ $assessment->status->value }}
                            </span>
                        </li>
                    @empty
                        <li>
                            <strong>Sem anamneses</strong>
                            <span>Nenhuma atividade clínica registrada.</span>
                        </li>
                    @endforelse
                </ol>
            </section>
        </div>
    </main>
@endsection
