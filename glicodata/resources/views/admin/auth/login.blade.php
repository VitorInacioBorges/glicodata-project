@extends('layouts.app')

@section('title', 'Acesso administrativo')
@section('body-class', 'gd-login-page')

@section('content')
    <main id="conteudo" class="gd-login-shell" aria-label="Login administrativo">
        <div class="row g-0">
            <div class="col-md-6">
                <section class="gd-login-summary">
                    <div class="gd-brand">
                        <img src="{{ asset('images/glicodata-mark.svg') }}" alt="">
                        <span><span class="gd-brand-accent">Glico</span>Data</span>
                    </div>

                    <div class="gd-login-copy">
                        <p class="text-uppercase fw-semibold small mb-3">Administração do sistema</p>
                        <h1>Gestão institucional segura.</h1>
                        <p class="mt-3 mb-0">Área reservada à administração global das unidades e dos registros de
                            auditoria.</p>
                    </div>

                    <p class="gd-login-footnote small mt-5 mb-0">Contas administrativas são independentes das contas das
                        UBS.</p>
                </section>
            </div>

            <div class="col-md-6">
                <section class="gd-login-form">
                    <p class="gd-eyebrow">Administrador autorizado</p>
                    <h2>Acessar a administração</h2>
                    <p class="text-secondary mt-2 mb-4">Informe o ID administrativo e a senha.</p>

                    @if (session('status'))
                        <div class="alert alert-success mb-4" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="account_type" value="admin">

                        <div class="mb-3">
                            <label class="form-label" for="identifier">ID de administrador</label>
                            <input class="form-control" id="identifier" name="identifier" type="text"
                                value="{{ old('identifier') }}" autocomplete="username" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password">Senha</label>
                            <input class="form-control" id="password" name="password" type="password"
                                autocomplete="current-password" required>
                        </div>

                        <button class="btn btn-primary gd-login-button w-100" type="submit">Entrar</button>
                    </form>

                    <a class="small mt-4" href="{{ route('ubs.login') }}">Voltar ao acesso da UBS</a>
                </section>
            </div>
        </div>
    </main>
@endsection
