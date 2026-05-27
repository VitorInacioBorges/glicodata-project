@extends('layouts.ubs')

@section('title', 'Painel')
@section('portal-header', true)

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-12">
        <section class="overflow-hidden rounded-3xl bg-slate-950 text-white ubs-panel-shadow">
            <div class="grid gap-10 px-6 py-9 sm:px-10 lg:grid-cols-[1fr_360px] lg:px-12 lg:py-12">
                <div>
                    <span class="rounded-full bg-teal-400/10 px-3 py-1.5 text-xs font-semibold uppercase tracking-widest text-teal-200">
                        Lobby da UBS
                    </span>
                    <h1 class="mt-6 max-w-xl text-3xl font-semibold tracking-tight sm:text-4xl">
                        Bem-vinda ao ambiente de atendimento da sua unidade.
                    </h1>
                    <p class="mt-4 max-w-xl leading-7 text-slate-300">
                        Acesse os cadastros clinicos e acompanhe as avaliacoes realizadas pela equipe da UBS.
                    </p>
                </div>

                <div class="rounded-2xl bg-white/6 p-5 ring-1 ring-white/10">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Unidade conectada</p>
                    <div class="mt-5 flex items-center gap-4">
                        <span class="grid size-12 place-items-center rounded-xl bg-teal-500/20 text-teal-200">
                            <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path d="M4 21h16M6.5 21V8.4L12 4l5.5 4.4V21M9 21v-4h6v4M12 8.5v5M9.5 11h5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <div>
                            <p class="font-semibold">Conta institucional UBS</p>
                            <p class="mt-1 text-sm text-slate-400">Area reservada da unidade</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-9" aria-labelledby="shortcuts-title">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-teal-700">Modulos</p>
                    <h2 id="shortcuts-title" class="mt-1 text-2xl font-semibold tracking-tight text-slate-950">Acesso rapido</h2>
                </div>
                <p class="hidden text-sm text-slate-500 sm:block">Escolha um modulo para visualizar sua estrutura.</p>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-3">
                <a href="{{ route('ubs.patients.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-900/5">
                    <span class="grid size-11 place-items-center rounded-xl bg-teal-50 text-teal-700">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M16 21v-2.2c0-2.1-1.8-3.8-4-3.8s-4 1.7-4 3.8V21M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 9v6M16 12h6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Pacientes</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Visualize a futura listagem de pacientes vinculados a unidade.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-teal-700">Acessar pagina <span class="transition group-hover:translate-x-1">&rarr;</span></span>
                </a>

                <a href="{{ route('ubs.doctors.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-900/5">
                    <span class="grid size-11 place-items-center rounded-xl bg-sky-50 text-sky-700">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M8 4v5a4 4 0 0 0 8 0V4M7 4h2m6 0h2M12 13v2a5 5 0 0 0 10 0v-2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="20" cy="12" r="2"/>
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Medicos</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Consulte a area destinada aos profissionais da UBS.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-teal-700">Acessar pagina <span class="transition group-hover:translate-x-1">&rarr;</span></span>
                </a>

                <a href="{{ route('ubs.assessments.index') }}" class="group rounded-2xl border border-slate-200 bg-white p-6 transition hover:-translate-y-0.5 hover:border-teal-200 hover:shadow-lg hover:shadow-teal-900/5">
                    <span class="grid size-11 place-items-center rounded-xl bg-amber-50 text-amber-700">
                        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M9 5h-2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 4h6v4H9zM9 13h6M9 17h4" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-slate-950">Avaliacoes</h3>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Acompanhe a estrutura de registros clinicos avaliativos.</p>
                    <span class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-teal-700">Acessar pagina <span class="transition group-hover:translate-x-1">&rarr;</span></span>
                </a>
            </div>
        </section>
    </main>
@endsection
