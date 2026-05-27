@extends('layouts.ubs')

@section('title', 'Pacientes')
@section('portal-header', true)

@section('content')
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold text-teal-700">Pacientes</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Pacientes da unidade</h1>
                <p class="mt-2 text-sm text-slate-500">Estrutura preparada para os pacientes associados a esta UBS.</p>
            </div>
            <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-xl bg-teal-700 px-5 py-3 text-sm font-semibold text-white opacity-55" title="Disponivel em uma proxima etapa">
                <span class="text-lg leading-none">+</span>
                Novo paciente
            </button>
        </div>

        <section class="mt-8 overflow-hidden rounded-2xl border border-slate-200 bg-white ubs-panel-shadow" aria-label="Lista de pacientes">
            <div class="flex flex-col gap-3 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex h-11 w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm text-slate-400 sm:max-w-sm">
                    <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m17 17-4-4m2-4.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z"/></svg>
                    Buscar paciente
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-500">Listagem pendente de integracao</span>
            </div>

            <div class="hidden grid-cols-[1.6fr_1fr_1fr_1fr] gap-4 border-b border-slate-100 bg-slate-50/70 px-6 py-4 text-xs font-semibold uppercase tracking-wider text-slate-500 md:grid">
                <span>Paciente</span><span>CPF</span><span>Idade</span><span>Contato</span>
            </div>

            <div class="grid min-h-80 place-items-center px-6 py-12 text-center">
                <div class="max-w-sm">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-teal-50 text-teal-700">
                        <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M16 21v-2.3a4 4 0 0 0-8 0V21M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM18 11h4M20 9v4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    <h2 class="mt-5 text-base font-semibold text-slate-900">Nenhum dado exibido ainda</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500">Os pacientes aparecerao nesta tabela quando a logica de listagem for implementada.</p>
                </div>
            </div>
        </section>
    </main>
@endsection
