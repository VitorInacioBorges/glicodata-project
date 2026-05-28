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
                        <p class="mt-3 mb-0">Ambiente reservado às unidades cadastradas para acompanhamento de pacientes e avaliações.</p>
                    </div>

                    <p class="gd-login-footnote small mt-5 mb-0">Acesso institucional protegido pelo serviço de identidade da UEPG.</p>
                </section>
            </div>

            <div class="col-md-6">
                <section class="gd-login-form">
                    <p class="gd-eyebrow">UBS autorizada</p>
                    <h2>Acessar o GlicoData</h2>
                    <p class="text-secondary mt-2 mb-4">Utilize a conta institucional vinculada à sua unidade.</p>

                    @if (session('auth_error'))
                        <div class="alert alert-danger mb-4" role="alert">
                            {{ session('auth_error') }}
                        </div>
                    @endif

                    <a class="btn btn-primary gd-login-button" href="{{ route('web.ubs.auth.redirect') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M14 8l4 4-4 4M18 12H7M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Entrar com Keycloak institucional
                    </a>

                    <div class="gd-login-notice mt-4">
                        O cadastro da UBS é previamente autorizado. O GlicoData não solicita nem armazena sua senha institucional nesta tela.
                    </div>
                </section>
            </div>
        </div>
    </main>
@endsection
