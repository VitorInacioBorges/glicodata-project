<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#08796b">

    <title>@yield('title', 'GlicoData') | GlicoData</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="@yield('body-class')">
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>

    @hasSection('protected-navigation')
        @php
            $unit = auth('ubs')->user();
        @endphp
        <header class="gd-header sticky-top">
            <nav class="navbar navbar-expand-lg" aria-label="Navegação principal">
                <div class="container-fluid px-3 px-md-4 py-2">
                    <a class="gd-brand" href="{{ route('ubs.lobby') }}" aria-label="GlicoData, início">
                        <img src="{{ asset('images/glicodata-mark.svg') }}" alt="">
                        <span><span class="gd-brand-accent">Glico</span>Data</span>
                    </a>

                    <button class="navbar-toggler gd-navbar-toggler" type="button" data-bs-toggle="collapse"
                        data-bs-target="#gdNavigation" aria-controls="gdNavigation" aria-expanded="false"
                        aria-label="Abrir navegação">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div id="gdNavigation" class="collapse navbar-collapse ms-lg-4">
                        <ul class="navbar-nav gd-nav me-auto my-3 my-lg-0">
                            <li class="nav-item">
                                <a @class(['nav-link', 'active' => request()->routeIs('ubs.patients.*')])
                                    href="{{ route('ubs.patients.index') }}">Pacientes</a>
                            </li>
                            <li class="nav-item">
                                <a @class(['nav-link', 'active' => request()->routeIs('ubs.professionals.*')])
                                    href="{{ route('ubs.professionals.index') }}">Profissionais</a>
                            </li>
                            <li class="nav-item">
                                <a @class(['nav-link', 'active' => request()->routeIs('ubs.assessments.*')])
                                    href="{{ route('ubs.assessments.index') }}">Anamneses</a>
                            </li>
                            <li class="nav-item">
                                <a @class(['nav-link', 'active' => request()->routeIs('ubs.reports.*')])
                                    href="{{ route('ubs.reports.index') }}">Relatórios</a>
                            </li>
                        </ul>

                        <div class="gd-session">
                            <div class="gd-session-unit">
                                Unidade autenticada
                                <strong>{{ $unit?->name ?? 'UBS' }}</strong>
                            </div>
                            <a class="btn btn-outline-primary btn-sm" href="{{ route('ubs.profile.edit') }}">Perfil</a>
                            <a class="btn btn-outline-primary btn-sm"
                                href="{{ route('ubs.password.edit') }}">Senha</a>
                            <form action="{{ route('ubs.logout') }}" method="POST">
                                @csrf
                                <button class="btn btn-outline-primary btn-sm" type="submit">Sair</button>
                            </form>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
    @endif

    @if (session('status'))
        <div class="container-fluid gd-flash mt-3" role="status">
            <div class="alert alert-success mb-0">{{ session('status') }}</div>
        </div>
    @endif

    @yield('content')
</body>

</html>
