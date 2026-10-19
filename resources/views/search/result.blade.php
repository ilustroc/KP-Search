@extends('layouts.app')

@section('title', 'Consulta de Clientes | KP SEARCH')

@section('body')
    <div class="kp-app-shell min-h-screen overflow-x-hidden">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto grid w-full max-w-[1560px] gap-3 px-4 py-3 sm:px-6 lg:grid-cols-[auto_minmax(0,1fr)] lg:items-center lg:gap-8 lg:px-8">
                <a href="{{ route('search.index') }}" class="inline-flex w-fit items-center rounded-lg outline-none transition hover:opacity-80 focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2" aria-label="Ir al buscador">
                    <img src="{{ asset('brand/kp-invest-logo.png') }}" alt="KP Invest" width="156" height="38" class="h-8 w-auto object-contain sm:h-9">
                </a>

                <form method="POST" action="{{ route('search.store') }}" class="grid min-w-0 gap-2 sm:grid-cols-[minmax(0,1fr)_auto]" novalidate data-search-form>
                    @csrf
                    <label for="documento" class="sr-only">Número de documento</label>
                    <div class="relative min-w-0 flex-1">
                        <x-ui.icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            id="documento"
                            name="documento"
                            type="text"
                            value="{{ old('documento', $document) }}"
                            inputmode="numeric"
                            autocomplete="off"
                            maxlength="8"
                            pattern="[0-9]{8}"
                            required
                            class="kp-input h-11 w-full rounded-xl border border-slate-300 bg-slate-50 pl-10 pr-3 text-sm tracking-wide text-slate-900 outline-none placeholder:text-slate-400 focus:border-kp-green focus:bg-white focus:ring-3 focus:ring-kp-green/10"
                            placeholder="Ej: 12345678"
                        >
                    </div>
                    <x-ui.button type="submit" label="Buscar" data-search-button class="h-11 shrink-0 px-5 text-sm focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2 sm:px-7">
                        <x-slot:icon><x-ui.icon name="search" /></x-slot:icon>
                    </x-ui.button>
                </form>
            </div>
            @error('documento')
                <p class="mx-auto w-full max-w-[1560px] px-4 pb-2.5 text-sm font-semibold text-red-700 sm:px-6 lg:px-8" role="alert">{{ $message }}</p>
            @enderror
        </header>

        <main class="mx-auto w-full max-w-[1560px] px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
            @if ($result['gold_query_failed'])
                <section class="kp-result-card px-5 py-8 text-center">
                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-600" aria-hidden="true">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 8v4"></path>
                            <path d="M12 16h.01"></path>
                            <path d="m10.3 3.9-7 12.1A2 2 0 0 0 5 19h14a2 2 0 0 0 1.7-3l-7-12.1a2 2 0 0 0-3.4 0Z"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-lg font-bold text-slate-900">No fue posible realizar la consulta. Inténtalo nuevamente.</h1>
                </section>
            @elseif ($result['person'] === null && $result['phones'] === [] && $result['emails'] === [] && $result['addresses'] === [] && $result['vehicles'] === [] && $result['properties'] === [] && $result['family'] === [] && $result['sbs_summary'] === [] && $result['sbs_accounts'] === [] && $result['essalud'] === [])
                <section class="kp-result-card px-5 py-10 text-center">
                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-hidden="true">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-lg font-bold text-slate-900">No se encontró información para el documento ingresado.</h1>
                    <p class="mt-1.5 text-sm text-slate-500">Verifique el número e intente nuevamente.</p>
                </section>
            @else
                @php($person = $result['person'])

                <section class="kp-profile-card px-5 py-5 sm:px-7 sm:py-6">
                    <div class="flex items-center gap-4 sm:gap-5">
                        <div class="flex size-18 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 sm:size-22" aria-hidden="true"><x-ui.icon name="user" size="lg" /></div>
                        @if ($person !== null)
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Ficha de cliente</p>
                                <h1 class="mt-1 break-words text-2xl font-bold leading-tight tracking-tight text-slate-950 sm:text-[34px]">{{ $person['nombre_completo'] }}</h1>
                                <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600 sm:text-base">
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-3 py-1.5"><x-ui.icon name="id-card" size="sm" class="text-slate-500" />Documento <strong class="font-semibold text-slate-900">{{ $person['documento'] }}</strong></span>
                                    <span class="inline-flex items-center gap-1.5 rounded-md bg-slate-100 px-3 py-1.5"><x-ui.icon name="clock" size="sm" class="text-slate-500" />Edad <strong class="font-semibold text-slate-900">{{ $person['edad'] ?? '—' }}</strong></span>
                                </div>
                            </div>
                        @else
                            <div><p class="text-xs font-bold uppercase tracking-[0.12em] text-slate-500">Ficha de cliente</p><p class="mt-1 text-base text-slate-600 sm:text-lg">Documento <strong class="font-semibold text-slate-900">{{ $document }}</strong></p></div>
                        @endif
                    </div>
                </section>

                <section class="mt-5" data-result-tabs>
                    <div class="inline-flex max-w-full gap-1 rounded-lg border border-slate-200 bg-white p-1" role="tablist" aria-label="Información del cliente">
                        <button type="button" id="tab-principal" role="tab" aria-selected="true" aria-controls="panel-principal" data-result-tab="principal" class="inline-flex items-center gap-2 rounded-md bg-kp-green-dark px-4 py-2 text-sm font-semibold text-white transition focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-1"><x-ui.icon name="user" />Principal</button>
                        <button type="button" id="tab-financial" role="tab" aria-selected="false" aria-controls="panel-financial" tabindex="-1" data-result-tab="financial" class="inline-flex items-center gap-2 rounded-md bg-transparent px-4 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-1"><x-ui.icon name="chart" />Situación financiera</button>
                    </div>

                    <div id="panel-principal" role="tabpanel" aria-labelledby="tab-principal" data-result-panel="principal" class="pt-4">
                        <div class="grid min-w-0 gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)] lg:items-start">
                            <div class="grid min-w-0 gap-4">
                                @include('search.partials.personal', ['person' => $person])
                                @include('search.partials.emails', ['emails' => $result['emails']])
                            </div>
                            <div class="grid min-w-0 gap-4">
                                @include('search.partials.phones', ['phones' => $result['phones']])
                                @include('search.partials.addresses', ['addresses' => $result['addresses']])
                                @include('search.partials.vehicles', ['vehicles' => $result['vehicles']])
                                @include('search.partials.properties', ['properties' => $result['properties']])
                                @include('search.partials.family', ['family' => $result['family']])
                            </div>
                        </div>
                    </div>

                    <div id="panel-financial" role="tabpanel" aria-labelledby="tab-financial" data-result-panel="financial" class="pt-4" hidden>
                        @include('search.partials.financial', ['summary' => $result['sbs_summary'], 'accounts' => $result['sbs_accounts'], 'essalud' => $result['essalud']])
                    </div>
                </section>
            @endif
        </main>
    </div>
@endsection
