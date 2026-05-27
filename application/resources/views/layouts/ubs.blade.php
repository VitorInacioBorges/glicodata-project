<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="theme-color" content="#0f766e">
        <title>@yield('title', 'Glicodata') | Portal UBS</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="ubs-surface min-h-full font-sans text-slate-900 antialiased">
        @hasSection('portal-header')
            <header class="sticky top-0 z-20 border-b border-emerald-950/5 bg-white/90 backdrop-blur-xl">
                <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('ubs.lobby') }}" class="shrink-0 text-xl font-bold tracking-tight text-slate-950" aria-label="Glicodata, inicio">
                        <span class="text-teal-700">Glico</span>data
                    </a>

                    <nav class="mx-auto hidden items-center rounded-full bg-slate-100/80 p-1 md:flex" aria-label="Navegacao UBS">
                        <a href="{{ route('ubs.patients.index') }}"
                            @class([
                                'rounded-full px-5 py-2 text-sm font-medium transition',
                                'bg-white text-teal-800 shadow-sm' => request()->routeIs('ubs.patients.*'),
                                'text-slate-600 hover:text-slate-950' => ! request()->routeIs('ubs.patients.*'),
                            ])>
                            Pacientes
                        </a>
                        <a href="{{ route('ubs.doctors.index') }}"
                            @class([
                                'rounded-full px-5 py-2 text-sm font-medium transition',
                                'bg-white text-teal-800 shadow-sm' => request()->routeIs('ubs.doctors.*'),
                                'text-slate-600 hover:text-slate-950' => ! request()->routeIs('ubs.doctors.*'),
                            ])>
                            Medicos
                        </a>
                        <a href="{{ route('ubs.assessments.index') }}"
                            @class([
                                'rounded-full px-5 py-2 text-sm font-medium transition',
                                'bg-white text-teal-800 shadow-sm' => request()->routeIs('ubs.assessments.*'),
                                'text-slate-600 hover:text-slate-950' => ! request()->routeIs('ubs.assessments.*'),
                            ])>
                            Avaliacoes
                        </a>
                    </nav>

                    <div class="ml-auto flex items-center gap-3 rounded-full border border-emerald-100 bg-emerald-50/70 py-1.5 pl-3 pr-1.5">
                        <div class="hidden text-right sm:block">
                            <p class="text-xs font-medium text-emerald-800">Conta UBS</p>
                            <p class="text-xs text-slate-500">Unidade de saude</p>
                        </div>
                        <span class="grid size-10 place-items-center rounded-full bg-teal-700 text-white" aria-label="Perfil da unidade basica de saude">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path d="M4 21h16M6.5 21V8.4L12 4l5.5 4.4V21M9 21v-4h6v4" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M12 8.5v5M9.5 11h5" stroke-linecap="round"/>
                            </svg>
                        </span>
                    </div>
                </div>

                <nav class="flex justify-center gap-2 border-t border-slate-100 px-4 py-3 md:hidden" aria-label="Navegacao UBS mobile">
                    <a href="{{ route('ubs.patients.index') }}" @class(['rounded-full px-4 py-2 text-sm font-medium', 'bg-teal-700 text-white' => request()->routeIs('ubs.patients.*'), 'bg-slate-100 text-slate-700' => ! request()->routeIs('ubs.patients.*')])>Pacientes</a>
                    <a href="{{ route('ubs.doctors.index') }}" @class(['rounded-full px-4 py-2 text-sm font-medium', 'bg-teal-700 text-white' => request()->routeIs('ubs.doctors.*'), 'bg-slate-100 text-slate-700' => ! request()->routeIs('ubs.doctors.*')])>Medicos</a>
                    <a href="{{ route('ubs.assessments.index') }}" @class(['rounded-full px-4 py-2 text-sm font-medium', 'bg-teal-700 text-white' => request()->routeIs('ubs.assessments.*'), 'bg-slate-100 text-slate-700' => ! request()->routeIs('ubs.assessments.*')])>Avaliacoes</a>
                </nav>
            </header>
        @endif

        @yield('content')
    </body>
</html>
