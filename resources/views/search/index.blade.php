@extends('layouts.app')

@section('title', 'Consulta de Clientes | KP SEARCH')

@section('body')
    <main class="flex min-h-screen items-center justify-center px-4 py-8 sm:px-6">
        <section class="w-full max-w-xl rounded-xl border border-slate-200/80 bg-white px-6 py-8 shadow-[0_8px_30px_rgba(15,23,42,0.06)] sm:px-9 sm:py-10">
            <div class="text-center">
                <img
                    src="{{ asset('brand/kp-invest-logo.png') }}"
                    alt="KP Invest"
                    width="230"
                    height="55"
                    class="mx-auto h-12 w-auto object-contain sm:h-14"
                >
                <h1 class="mt-7 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Consulta de Clientes</h1>
                <p class="mt-2 text-sm text-slate-500 sm:text-base">Ingrese el número de documento que desea consultar.</p>
            </div>

            <form method="POST" action="{{ route('search.store') }}" class="mt-8" novalidate>
                @csrf
                <label for="documento" class="block text-xs font-bold uppercase tracking-[0.1em] text-slate-600">Número de documento</label>
                <div class="mt-2.5 flex flex-col gap-2.5 sm:flex-row">
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
                        class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-4 py-3 text-base tracking-wide text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-kp-green focus:ring-3 focus:ring-kp-green/10"
                        placeholder="Ej: 12345678"
                    >
                    <button type="submit" class="rounded-lg bg-kp-green-dark px-7 py-3 text-sm font-bold text-white transition hover:bg-kp-green-deep focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2">Consultar</button>
                </div>
                @error('documento')
                    <p class="mt-2.5 text-sm font-semibold text-red-700" role="alert">{{ $message }}</p>
                @enderror
            </form>

            <p class="mt-8 border-t border-slate-100 pt-5 text-center text-xs text-slate-400">Uso interno de KP INVEST</p>
        </section>
    </main>
@endsection
