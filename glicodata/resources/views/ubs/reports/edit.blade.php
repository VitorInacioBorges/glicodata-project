@extends('layouts.app')

@section('title', 'Editar relatório')
@section('protected-navigation', 'true')

@section('content')
    <main id="conteudo" class="gd-page">
        <p class="gd-eyebrow">Relatórios</p>
        <h1 class="gd-heading">Editar relatório</h1>

        <section class="gd-panel gd-form-panel mt-4">
            <form method="POST" action="{{ route('ubs.reports.update', $report) }}">
                @csrf
                @method('PUT')
                @include('ubs.reports._form')
            </form>
        </section>
    </main>
@endsection
