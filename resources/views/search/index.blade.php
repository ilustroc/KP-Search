@extends('layouts.app')

@section('title', 'Buscar | KP SEARCH')

@section('content')
    <section class="mx-auto max-w-4xl py-12 text-center lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-kp-green-dark">KP INVEST</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-kp-blue sm:text-5xl">KP SEARCH</h1>
        <p class="mt-4 text-lg text-slate-600">Consulta centralizada de información</p>

        <form method="POST" action="{{ route('search.store') }}" class="mx-auto mt-12 max-w-3xl text-left">
            @csrf
            <label for="document" class="block text-sm font-semibold text-slate-700">DNI o RUC</label>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <input
                    id="document"
                    name="document"
                    type="text"
                    value="{{ old('document') }}"
                    inputmode="numeric"
                    autocomplete="off"
                    maxlength="11"
                    pattern="(?:[0-9]{8}|[0-9]{11})"
                    required
                    aria-describedby="document-help"
                    class="min-w-0 flex-1 rounded-lg border border-slate-300 bg-white px-5 py-4 text-lg tracking-wide text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-kp-green-dark focus:ring-3 focus:ring-kp-green/15"
                    placeholder="Ingrese DNI o RUC"
                >
                <button type="submit" class="rounded-lg bg-kp-green-dark px-8 py-4 text-base font-semibold text-white transition hover:bg-kp-green-deep focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2">
                    Buscar
                </button>
            </div>
            <div id="document-help" class="mt-3 flex flex-col gap-1 text-sm text-slate-500 sm:flex-row sm:justify-between">
                <span>DNI: 8 dígitos · RUC: 11 dígitos</span>
                <span>El documento no aparecerá en la URL.</span>
            </div>
            @error('document')
                <p class="mt-3 text-sm font-semibold text-red-700">{{ $message }}</p>
            @enderror
        </form>
    </section>
@endsection
