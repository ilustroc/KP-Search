@extends('layouts.app')

@section('title', 'Resultado | KP SEARCH')

@section('content')
    <section data-search-tabs>
        <div class="flex flex-col justify-between gap-6 border-b border-slate-200 pb-8 lg:flex-row lg:items-end">
            <div>
                <a href="{{ route('search.index') }}" class="text-sm font-semibold text-kp-green-dark hover:underline">← Nueva búsqueda</a>
                <p class="mt-6 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Consulta protegida</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-kp-blue">Información personal</h1>
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600">
                    <span><strong class="text-slate-800">{{ $documentType }}:</strong> {{ $maskedDocument }}</span>
                    <span class="hidden h-4 w-px bg-slate-300 sm:block"></span>
                    <span><strong class="text-slate-800">Última actualización:</strong> No disponible</span>
                </div>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                El contrato Gold se encuentra pendiente de publicación.
            </div>
        </div>

        <div class="mt-8 overflow-x-auto border-b border-slate-200" role="tablist" aria-label="Módulos de información">
            <div class="flex min-w-max gap-1">
                @foreach ($modules as $key => $module)
                    <button
                        type="button"
                        role="tab"
                        aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                        aria-controls="search-module-panel"
                        tabindex="{{ $loop->first ? '0' : '-1' }}"
                        data-module-tab
                        data-module="{{ $key }}"
                        data-url="{{ route('search.results.modules.show', ['token' => $token, 'module' => $key]) }}"
                        class="border-b-2 px-4 py-3 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-inset focus:ring-kp-green-dark {{ $loop->first ? 'border-kp-green-dark text-kp-green-dark' : 'border-transparent text-slate-600 hover:border-slate-300 hover:text-slate-900' }}"
                    >
                        {{ $module['label'] }}
                    </button>
                @endforeach
            </div>
        </div>

        <div id="search-module-panel" class="min-h-72 py-10" role="tabpanel" aria-live="polite"></div>

        <template data-module-state-template="loading">
            @include('search.partials.module-state', ['state' => 'loading'])
        </template>
        <template data-module-state-template="empty">
            @include('search.partials.module-state', ['state' => 'empty'])
        </template>
        <template data-module-state-template="error">
            @include('search.partials.module-state', ['state' => 'error'])
        </template>
        <template data-module-state-template="forbidden">
            @include('search.partials.module-state', ['state' => 'forbidden'])
        </template>
        <template data-module-state-template="unavailable">
            @include('search.partials.module-state', ['state' => 'unavailable'])
        </template>
    </section>
@endsection
