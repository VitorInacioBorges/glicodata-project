@extends('layouts.app')

@section('title', 'Gerenciar UBS')

@section('content')
    <main id="conteudo" class="gd-page">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
            <div>
                <p class="gd-eyebrow">Administração global</p>
                <h1 class="gd-heading">Gerenciar UBS</h1>
                <p class="gd-subtitle">A ativação libera o login por CNES e o acesso aos recursos clínicos da unidade.</p>
            </div>
            <a class="btn btn-outline-primary" href="{{ route('admin.ubs.index') }}">Voltar para a lista</a>
        </div>

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">Revise os campos destacados.</div>
        @endif

        <section class="gd-panel gd-panel-shadow p-4">
            <form method="POST" action="{{ route('admin.ubs.update', $ubs) }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="cnes">CNES</label>
                        <input @class(['form-control', 'is-invalid' => $errors->has('cnes')]) id="cnes" name="cnes" type="text"
                            value="{{ old('cnes', $ubs->cnes) }}" inputmode="numeric" pattern="[0-9]{7}" maxlength="7"
                            required>
                        @error('cnes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" id="is_active" name="is_active" type="checkbox" value="1"
                                @checked((bool) old('is_active', $ubs->is_active))>
                            <label class="form-check-label" for="is_active">UBS ativa</label>
                        </div>
                    </div>
                </div>

                @include('ubs.partials.profile-fields', ['ubs' => $ubs, 'districts' => $districts])

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Salvar alterações</button>
                    <a class="btn btn-outline-primary" href="{{ route('admin.ubs.index') }}">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
@endsection
