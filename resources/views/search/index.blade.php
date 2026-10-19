@extends('layouts.app')

@section('title', 'Consulta de Clientes | KP SEARCH')

@section('body')
    <main class="kp-app-shell flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
        <section class="kp-search-card w-full max-w-xl px-6 py-8 sm:px-10 sm:py-10">
            <div class="text-center">
                <img
                    src="{{ asset('brand/kp-invest-logo.png') }}"
                    alt="KP Invest"
                    width="230"
                    height="55"
                    class="mx-auto h-12 w-auto object-contain sm:h-14"
                >
                <h1 class="mt-5 text-3xl font-bold tracking-tight text-slate-950 sm:text-[34px]">Consulta de Clientes</h1>
                <p class="mx-auto mt-3 max-w-sm text-sm leading-6 text-slate-500 sm:text-base">Ingrese el número de documento que desea consultar.</p>
            </div>

            <form method="POST" action="{{ route('search.store') }}" class="mt-8" novalidate data-search-form>
                @csrf
                <label for="documento" class="block text-xs font-bold uppercase tracking-[0.12em] text-slate-600">Número de documento</label>
                <div class="relative mt-2.5">
                    <x-ui.icon name="id-card" size="lg" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input
                        id="documento"
                        name="documento"
                        type="text"
                        value="{{ old('documento') }}"
                        inputmode="numeric"
                        autocomplete="off"
                        maxlength="8"
                        pattern="[0-9]{8}"
                        required
                        autofocus
                        class="kp-input h-14 w-full rounded-xl border border-slate-300 bg-slate-50 pl-12 pr-4 text-lg tracking-[0.12em] text-slate-950 outline-none placeholder:text-base placeholder:tracking-normal placeholder:text-slate-400 focus:border-kp-green focus:bg-white focus:ring-4 focus:ring-kp-green/10"
                        placeholder="Ej: 12345678"
                    >
                </div>
                <x-ui.button type="submit" label="Consultar" full-width data-search-button class="mt-3 h-14 px-6 text-sm focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2">
                    <x-slot:icon><x-ui.icon name="search" /></x-slot:icon>
                </x-ui.button>
                @error('documento')
                    <p class="mt-3 text-sm font-semibold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </form>
        </section>
    </main>
@endsection
