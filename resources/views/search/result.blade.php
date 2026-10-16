@extends('layouts.app')

@section('title', 'Resultado de consulta | KP SEARCH')

@section('body')
    <div class="min-h-screen overflow-x-hidden bg-slate-100">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex min-h-20 w-full max-w-[1560px] flex-col items-stretch gap-3 px-4 py-3 sm:flex-row sm:items-center sm:gap-4 sm:px-6 lg:gap-7 lg:px-8">
                <a href="{{ route('search.index') }}" class="shrink-0 self-start" aria-label="Ir al buscador">
                    <img src="{{ asset('brand/kp-invest-logo.png') }}" alt="KP Invest" width="156" height="38" class="h-8 w-auto object-contain sm:h-9">
                </a>

                <form method="POST" action="{{ route('search.store') }}" class="flex min-w-0 flex-1 gap-2" novalidate>
                    @csrf
                    <label for="documento" class="sr-only">Número de documento</label>
                    <div class="relative min-w-0 flex-1">
                        <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4.5 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
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
                            class="h-11 w-full rounded-lg border border-slate-300 bg-slate-50 pl-10 pr-3 text-sm tracking-wide text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-kp-green focus:bg-white focus:ring-3 focus:ring-kp-green/10"
                            placeholder="Ej: 12345678"
                        >
                    </div>
                    <button type="submit" class="h-11 shrink-0 rounded-lg bg-kp-green-dark px-5 text-sm font-bold text-white transition hover:bg-kp-green-deep focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2 sm:px-7">Buscar</button>
                </form>
            </div>
            @error('documento')
                <p class="mx-auto w-full max-w-[1560px] px-4 pb-2.5 text-sm font-semibold text-red-700 sm:px-6 lg:px-8" role="alert">{{ $message }}</p>
            @enderror
        </header>

        <main class="mx-auto w-full max-w-[1560px] px-4 py-5 sm:px-6 lg:px-8 lg:py-6">
            @if ($result['gold_query_failed'])
                <section class="rounded-lg border border-amber-200 bg-amber-50 px-5 py-8 text-center shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
                    <div class="mx-auto flex size-11 items-center justify-center rounded-full bg-amber-100 text-amber-700" aria-hidden="true">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M12 8v4"></path>
                            <path d="M12 16h.01"></path>
                            <path d="m10.3 3.9-7 12.1A2 2 0 0 0 5 19h14a2 2 0 0 0 1.7-3l-7-12.1a2 2 0 0 0-3.4 0Z"></path>
                        </svg>
                    </div>
                    <h1 class="mt-4 text-lg font-bold text-slate-900">No fue posible realizar la consulta en este momento.</h1>
                    <p class="mt-1.5 text-sm text-slate-600">Intente nuevamente en unos minutos.</p>
                </section>
            @elseif ($result['person'] === null && $result['phones'] === [])
                <section class="rounded-lg border border-slate-200 bg-white px-5 py-10 text-center shadow-[0_2px_10px_rgba(15,23,42,0.04)]">
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

                <section class="rounded-lg border border-slate-200 bg-white px-5 py-5 shadow-[0_2px_10px_rgba(15,23,42,0.04)] sm:px-6">
                    <div class="flex items-center gap-4 sm:gap-5">
                        <div class="flex size-20 shrink-0 items-center justify-center rounded-full bg-slate-100 text-kp-blue sm:size-22" aria-hidden="true">
                            <svg class="size-10 sm:size-11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="8" r="4"></circle>
                                <path d="M4 21a8 8 0 0 1 16 0"></path>
                            </svg>
                        </div>
                        @if ($person !== null)
                            <div class="min-w-0 flex-1">
                                <h1 class="break-words text-2xl font-bold leading-tight tracking-tight text-slate-900 sm:text-[34px]">{{ $person['nombre_completo'] }}</h1>
                                <div class="mt-2.5 flex flex-wrap gap-x-7 gap-y-1.5 text-sm text-slate-600 sm:text-base">
                                    <span>Documento <strong class="font-semibold text-slate-900">{{ $person['documento'] }}</strong></span>
                                    <span>Edad <strong class="font-semibold text-slate-900">{{ $person['edad'] ?? '—' }}</strong></span>
                                </div>
                            </div>
                        @else
                            <p class="text-base text-slate-600 sm:text-lg">Documento <strong class="font-semibold text-slate-900">{{ $document }}</strong></p>
                        @endif
                    </div>
                </section>

                <section class="mt-4" data-result-tabs>
                    <div class="inline-flex max-w-full gap-1 rounded-lg border border-slate-200 bg-white p-1 shadow-[0_1px_4px_rgba(15,23,42,0.04)]" role="tablist" aria-label="Información del cliente">
                        <button type="button" id="tab-principal" role="tab" aria-selected="true" aria-controls="panel-principal" data-result-tab="principal" class="rounded-md bg-kp-green-dark px-4 py-2 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-1">Principal</button>
                        <button type="button" id="tab-financial" role="tab" aria-selected="false" aria-controls="panel-financial" tabindex="-1" data-result-tab="financial" class="rounded-md bg-transparent px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-1">Situación financiera</button>
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
