@extends('layouts.app')

@section('title', 'Cadastro de UBS')
@section('body-class', 'gd-login-page')

@section('content')
    <main id="conteudo" class="gd-login-shell" aria-label="Cadastro de unidade de saúde">
        <div class="row g-0">
            <div class="col-md-6">
                <section class="gd-login-summary">
                    <div class="gd-brand">
                        <img src="{{ asset('images/glicodata-mark.svg') }}" alt="">
                        <span><span class="gd-brand-accent">Glico</span>Data</span>
                    </div>

                    <div class="gd-login-copy">
                        <p class="text-uppercase fw-semibold small mb-3">Cadastro institucional</p>
                        <h1>Solicite o acesso da sua UBS.</h1>
                        <p class="mt-3 mb-0">Informe o CNES e crie uma senha forte. A unidade ficará pendente até a revisão
                            de um administrador.</p>
                    </div>

                    <p class="gd-login-footnote small mt-5 mb-0">O CNES será usado como identificador de acesso da unidade.
                    </p>
                </section>
            </div>

            <div class="col-md-6">
                <section class="gd-login-form">
                    <p class="gd-eyebrow">Nova unidade</p>
                    <h2>Cadastrar UBS</h2>
                    <p class="text-secondary mt-2 mb-4">O formulário possui somente os dados necessários para criar a conta.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4" role="alert">Revise os campos destacados.</div>
                    @endif

                    <form method="POST" action="{{ route('ubs.register.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="cnes">CNES</label>
                            <input @class(['form-control', 'is-invalid' => $errors->has('cnes')]) id="cnes" name="cnes" type="text"
                                value="{{ old('cnes') }}" inputmode="numeric" pattern="[0-9]{7}" maxlength="7"
                                autocomplete="username" aria-describedby="cnes-help" required autofocus>
                            <div id="cnes-help" class="form-text">Informe os sete dígitos, incluindo zeros iniciais.</div>
                            @error('cnes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Senha</label>
                            <input @class(['form-control', 'is-invalid' => $errors->has('password')]) id="password" name="password" type="password"
                                autocomplete="new-password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="password_confirmation">Repita a senha</label>
                            <input class="form-control" id="password_confirmation" name="password_confirmation"
                                type="password" autocomplete="new-password" required>
                        </div>

                        <button class="btn btn-primary gd-login-button w-100" type="submit">Enviar cadastro</button>
                    </form>

                    <div class="gd-login-notice mt-4">A senha é armazenada somente como hash não reversível.</div>
                    <a class="small mt-4" href="{{ route('ubs.login') }}">Voltar para o acesso da UBS</a>
                </section>
            </div>
        </div>
    </main>
@endsection
