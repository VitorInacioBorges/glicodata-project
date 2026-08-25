@extends('layouts.app')

@section('title', 'Nova anamnese')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <p class="gd-eyebrow">Anamnese</p>
        <h1 class="gd-heading">Iniciar preenchimento</h1>
        <p class="gd-subtitle">
            {{ $questionnaireVersion->questionnaire->title }} · versão {{ $questionnaireVersion->version }}
        </p>

        <section class="gd-panel gd-form-panel mt-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($patients->isEmpty())
                <div class="alert alert-warning">Cadastre um paciente antes de iniciar uma anamnese.</div>
                <a class="btn btn-primary" href="{{ route('ubs.patients.create') }}">Cadastrar paciente</a>
            @else
                <form method="POST" action="{{ route('ubs.assessments.store') }}">
                    @csrf
                    <input type="hidden" name="questionnaire_version_id" value="{{ $questionnaireVersion->id }}">

                    <div class="mb-3">
                        <label class="form-label" for="patient_id">Paciente</label>
                        <select class="form-select" id="patient_id" name="patient_id" required>
                            <option value="">Selecione</option>
                            @foreach ($patients as $patient)
                                <option value="{{ $patient->id }}" @selected(old('patient_id', request('patient_id')) === $patient->id)>
                                    {{ $patient->first_name }} · {{ $patient->neighborhood }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @php
                        $selectedProfessional = $professionals->firstWhere('id', old('professional_id'));
                    @endphp
                    <div class="mb-3 gd-professional-search"
                        data-professional-search
                        data-search-url="{{ route('ubs.professionals.search') }}">
                        <label class="form-label" for="professional_search">Profissional responsável</label>
                        <input type="hidden" name="professional_id" value="{{ old('professional_id') }}"
                            data-professional-value required>
                        <input class="form-control" id="professional_search" type="search"
                            value="{{ $selectedProfessional ? $selectedProfessional->first_name . ' · ' . $selectedProfessional->specialty : '' }}"
                            placeholder="Pesquise pelo primeiro nome ou especialidade"
                            autocomplete="off" aria-autocomplete="list" aria-controls="professional_results"
                            data-professional-input required>
                        <div class="form-text">O registro guarda somente o profissional selecionado, sem dados pessoais.</div>
                        <div id="professional_results" class="gd-search-results" role="listbox"
                            data-professional-results hidden>
                            @foreach ($professionals as $professional)
                                <button type="button" role="option" data-professional-option
                                    data-id="{{ $professional->id }}"
                                    data-label="{{ $professional->first_name }} · {{ $professional->specialty }}">
                                    <strong>{{ $professional->first_name }}</strong>
                                    <span>{{ $professional->specialty }}</span>
                                </button>
                            @endforeach
                        </div>
                        <p class="small text-secondary mt-2 mb-0" data-professional-status aria-live="polite"></p>
                    </div>

                    <button class="btn btn-primary" type="submit">Criar rascunho</button>
                </form>
            @endif
        </section>
    </main>
@endsection
