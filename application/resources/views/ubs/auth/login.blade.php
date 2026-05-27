@extends('layouts.ubs')

@section('title', 'Entrar')

@section('content')
    <main class="grid min-h-screen lg:grid-cols-[1.04fr_0.96fr]">
        <section class="ubs-login-art relative hidden overflow-hidden p-12 text-white lg:flex lg:flex-col lg:justify-between">
            <div class="absolute inset-0 opacity-15 ubs-grid" aria-hidden="true"></div>
            <a href="{{ route('home') }}" class="relative text-2xl font-bold tracking-tight">
                <span class="text-teal-200">Glico</span>data
            </a>

            <div class="relative max-w-xl">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm text-teal-50 ring-1 ring-white/20">
                    <span class="size-2 rounded-full bg-teal-300"></span>
                    Portal institucional
                </span>
                <h1 class="mt-8 text-5xl font-semibold leading-tight tracking-tight">
                    Cuidado conectado para cada unidade.
                </h1>
                <p class="mt-6 max-w-md text-lg leading-8 text-teal-50/80">
                    Acompanhe pacientes, profissionais e avaliacoes em um ambiente pensado para as UBSs.
                </p>
            </div>

            <p class="relative text-sm text-teal-100/70">Dados protegidos e acesso institucional seguro.</p>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-12 sm:px-10">
            <div class="w-full max-w-md">
                <a href="{{ route('home') }}" class="mb-12 inline-block text-2xl font-bold tracking-tight text-slate-950 lg:hidden">
                    <span class="text-teal-700">Glico</span>data
                </a>

                <div class="flex size-14 items-center justify-center rounded-2xl bg-teal-700 text-white shadow-lg shadow-teal-700/20">
                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                        <path d="M4 21h16M6.5 21V8.4L12 4l5.5 4.4V21M9 21v-4h6v4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 8.5v5M9.5 11h5" stroke-linecap="round"/>
                    </svg>
                </div>

                <h2 class="mt-7 text-3xl font-semibold tracking-tight text-slate-950">Acesso da UBS</h2>
                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Entre com a conta institucional vinculada a sua Unidade Basica de Saude.
                </p>

                <form action="{{ route('web') }}" method="POST" class="mt-9">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-3 rounded-xl bg-teal-700 px-6 py-4 text-sm font-semibold text-white shadow-lg shadow-teal-700/15 transition hover:bg-teal-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-700">
                        Entrar com conta institucional
                        <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M4 10h12m-5-5 5 5-5 5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-8 rounded-xl border border-emerald-100 bg-white p-4 text-sm leading-6 text-slate-600 ubs-panel-shadow">
                    <p class="font-medium text-slate-900">Acesso exclusivo para UBS cadastrada</p>
                    <p class="mt-1">A autenticacao utiliza o cadastro institucional seguro da unidade.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
