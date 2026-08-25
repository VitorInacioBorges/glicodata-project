@extends('layouts.app')

@section('title', 'Administração')

@section('content')
    <main id="conteudo" class="gd-page">
        <section class="gd-hero" aria-labelledby="admin-title">
            <div>
                <p class="gd-eyebrow text-white-50">Administração global</p>
                <h1 id="admin-title">Painel administrativo</h1>
                <p>A conta está pronta para operar as APIs de unidades e auditoria.</p>
            </div>
            <div class="gd-hero-unit">
                <small>Sessão ativa</small>
                <strong>{{ auth('admin')->user()?->admin_code ?? 'Administrador' }}</strong>
                <div class="small text-white-50 mt-1">Autenticação local segura</div>
            </div>
        </section>

        <section class="gd-panel gd-panel-shadow p-4" aria-labelledby="admin-actions">
            <h2 id="admin-actions" class="gd-heading fs-4">Conta administrativa</h2>
            <p class="text-secondary">Revise cadastros pendentes, ative unidades e mantenha os dados institucionais das UBS.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-primary" href="{{ route('admin.ubs.index') }}">Gerenciar UBS</a>
                <a class="btn btn-outline-primary" href="{{ route('ubs.register') }}">Cadastrar UBS</a>
                <a class="btn btn-outline-primary" href="{{ route('admin.password.edit') }}">Alterar senha</a>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button class="btn btn-primary" type="submit">Sair</button>
                </form>
            </div>
        </section>
    </main>
@endsection
