@extends('layouts.app')

@section('title', 'Acesso do profissional')
@section('body-class', 'gd-login-page')

@section('content')
    <main id="conteudo" class="gd-login-shell" aria-label="Login individual do profissional">
        <div class="row g-0">
            <div class="col-md-6">
                <section class="gd-login-summary">
                    <div class="gd-brand">
                        <img src="{{ asset('images/glicodata-mark.svg') }}" alt="">
                        <span><span class="gd-brand-accent">Glico</span>Data</span>
                    </div>
                    <div class="gd-login-copy">
                        <p class="text-uppercase fw-semibold small mb-3">Identidade individual</p>
                        <h1>Anamneses com autoria e responsabilidade.</h1>
                        <p class="mt-3 mb-0">Entre com sua conta profissional. Cada alteração clínica fica vinculada à sua
                            identidade e à sua UBS.</p>
                    </div>
                </section>
            </div>
            <div class="col-md-6">
                <section class="gd-login-form">
                    <p class="gd-eyebrow">Profissional autorizado</p>
                    <h2>Acessar área clínica</h2>
                    <p class="text-secondary mt-2 mb-4">Use o e-mail e a senha fornecidos pelo gestor da unidade.</p>

                    @if (session('status'))
                        <div class="alert alert-success mb-4" role="status">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="account_type" value="user">
                        <div class="mb-3">
                            <label class="form-label" for="identifier">E-mail</label>
                            <input class="form-control" id="identifier" name="identifier" type="email"
                                value="{{ old('identifier') }}" autocomplete="username" required autofocus>
                        </div>
                        <div class="mb-4">
                            <label class="form-label" for="password">Senha</label>
                            <input class="form-control" id="password" name="password" type="password"
                                autocomplete="current-password" required>
                        </div>
                        <button class="btn btn-primary gd-login-button w-100" type="submit">Entrar</button>
                    </form>

                    <a class="small mt-4" href="{{ route('ubs.login') }}">Acesso institucional da UBS</a>
                    <a class="small mt-2" href="{{ route('admin.login') }}">Acesso administrativo global</a>
                </section>
            </div>
        </div>
    </main>
@endsection
