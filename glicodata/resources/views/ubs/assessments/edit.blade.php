@extends('layouts.app')

@section('title', 'Preencher anamnese')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex justify-content-between gap-3">
            <div>
                <p class="gd-eyebrow">Rascunho</p>
                <h1 class="gd-heading">Anamnese de {{ $assessment->patient->first_name }}</h1>
                <p class="gd-subtitle">
                    {{ $assessment->questionnaireVersion->questionnaire->title }} · versão
                    {{ $assessment->questionnaireVersion->version }} · profissional:
                    {{ $assessment->professional->first_name }} ({{ $assessment->professional->specialty }})
                </p>
            </div>
            <span class="gd-status gd-status-warning align-self-start">Rascunho</span>
        </div>

        <section class="gd-panel gd-form-panel mt-4">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Não foi possível salvar ou concluir.</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('ubs.assessments.update', $assessment) }}">
                @csrf
                @method('PUT')

                <h2 class="h5 mb-4">Questionário</h2>
                @include('ubs.assessments._questionnaire')

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-outline-primary" type="submit">Salvar rascunho</button>
                    <button
                        class="btn btn-primary"
                        type="submit"
                        formaction="{{ route('ubs.assessments.complete', $assessment) }}"
                        data-confirm="Concluir a anamnese? As respostas ficarão imutáveis e o risco será calculado no servidor."
                    >Concluir e calcular risco</button>
                    <a class="btn btn-outline-secondary" href="{{ route('ubs.assessments.index') }}">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
@endsection
