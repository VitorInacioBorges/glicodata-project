@extends('layouts.app')

@section('title', 'Novo relatório')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <p class="gd-eyebrow">Relatórios</p>
        <h1 class="gd-heading">Novo relatório</h1>

        <section class="gd-panel gd-form-panel mt-4">
            @if ($assessments->isEmpty())
                <div class="alert alert-warning">Não há anamneses concluídas sem relatório.</div>
                <a class="btn btn-primary" href="{{ route('ubs.assessments.index') }}">Ver anamneses</a>
            @else
                <form method="POST" action="{{ route('ubs.reports.store') }}">
                    @csrf
                    @include('ubs.reports._form')
                </form>
            @endif
        </section>
    </main>
@endsection
