@extends('layouts.app')

@section('title', 'Alterar senha')

@section('content')
    @php
        $isUbs = $accountType === 'ubs';
        $updateRoute = $isUbs ? 'ubs.password.update' : 'admin.password.update';
        $backRoute = $isUbs ? 'ubs.lobby' : 'admin.dashboard';
    @endphp

    <main id="conteudo" class="gd-page">
        <section class="gd-panel gd-panel-shadow p-4 mx-auto" style="max-width: 38rem" aria-labelledby="password-title">
            <p class="gd-eyebrow">Segurança da conta</p>
            <h1 id="password-title" class="gd-heading">Alterar senha</h1>
            <p class="text-secondary mb-4">A alteração encerrará a sessão atual e revogará todos os tokens da conta.</p>

            @if ($errors->any())
                <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route($updateRoute) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label" for="current_password">Senha atual</label>
                    <input class="form-control" id="current_password" name="current_password" type="password"
                        autocomplete="current-password" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Nova senha</label>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="new-password"
                        required>
                    <div class="form-text">Use ao menos 12 caracteres, maiúsculas, minúsculas, números e símbolos.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="password_confirmation">Confirme a nova senha</label>
                    <input class="form-control" id="password_confirmation" name="password_confirmation" type="password"
                        autocomplete="new-password" required>
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Atualizar senha</button>
                    <a class="btn btn-outline-secondary" href="{{ route($backRoute) }}">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
@endsection
