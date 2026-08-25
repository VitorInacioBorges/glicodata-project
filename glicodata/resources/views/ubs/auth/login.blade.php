@extends('layouts.app')

@section('title', 'Acesso da UBS')
@section('body-class', 'gd-login-page')

@section('content')
    <main id="conteudo" class="gd-login-shell" aria-label="Login institucional">
        <div class="row g-0">
            <div class="col-md-6">
                <section class="gd-login-summary">
                    <div class="gd-brand">
                        <img src="{{ asset('images/glicodata-mark.svg') }}" alt="">
                        <span><span class="gd-brand-accent">Glico</span>Data</span>
                    </div>

                    <div class="gd-login-copy">
                        <p class="text-uppercase fw-semibold small mb-3">Portal das unidades de saúde</p>
                        <h1>Registro clínico para atenção primária.</h1>
                        <p class="mt-3 mb-0">Ambiente reservado às unidades cadastradas para acompanhamento de pacientes e
                            avaliações.</p>
                    </div>

                    <p class="gd-login-footnote small mt-5 mb-0">Acesso protegido pela autenticação nativa do GlicoData.</p>
                </section>
            </div>

            <div class="col-md-6">
                <section class="gd-login-form">
                    <p class="gd-eyebrow">UBS autorizada</p>
                    <h2>Acessar o GlicoData</h2>
                    <p class="text-secondary mt-2 mb-4">Utilize a conta cadastrada para sua unidade.</p>

                    @if (session('status'))
                        <div class="alert alert-success mb-4" role="status">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="account_type" value="ubs">

                        <div class="mb-3">
                            <label class="form-label" for="identifier">CNES</label>
                            <input class="form-control" id="identifier" name="identifier" type="text"
                                value="{{ old('identifier') }}" inputmode="numeric" pattern="[0-9]{7}" maxlength="7"
                                autocomplete="username" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password">Senha</label>
                            <input class="form-control" id="password" name="password" type="password"
                                autocomplete="current-password" required>
                        </div>

                        <button class="btn btn-primary gd-login-button w-100" type="submit">Entrar</button>
                    </form>

                    <div class="gd-login-notice mt-4">
                        Senhas são armazenadas somente como hashes não reversíveis.
                    </div>

                    <a class="small mt-4" href="{{ route('admin.login') }}">Acesso administrativo global</a>
                    <a class="small mt-2" href="{{ route('ubs.register') }}">Cadastrar uma UBS</a>
                </section>
            </div>
        </div>
    </main>
@endsection
