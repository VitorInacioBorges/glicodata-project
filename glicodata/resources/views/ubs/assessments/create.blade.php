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
                                    {{ $patient->name }} · {{ $patient->birth->format('d/m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="symptoms">Sintomas e observações iniciais</label>
                        <textarea class="form-control" id="symptoms" name="symptoms" rows="4" maxlength="5000">{{ old('symptoms') }}</textarea>
                    </div>

                    <button class="btn btn-primary" type="submit">Criar rascunho</button>
                </form>
            @endif
        </section>
    </main>
@endsection
