@extends('layouts.guest')

@section('title', 'Iniciar sesión | KP SEARCH')

@section('content')
    <div>
        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-kp-green-dark">KP SEARCH</p>
        <h2 class="mt-3 text-3xl font-semibold tracking-tight text-slate-950">Iniciar sesión</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">Ingrese con sus credenciales corporativas.</p>
    </div>

    <form method="POST" action="{{ route('login.store') }}" class="mt-9 flex flex-col gap-6">
        @csrf

        <div>
            <label for="email" class="block text-sm font-semibold text-slate-700">Correo electrónico</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                autofocus
                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-950 outline-none transition placeholder:text-slate-400 focus:border-kp-green-dark focus:ring-3 focus:ring-kp-green/15"
                placeholder="nombre@kpinvest.pe"
            >
            @error('email')
                <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex items-center justify-between gap-4">
                <label for="password" class="block text-sm font-semibold text-slate-700">Contraseña</label>
                <span class="text-xs text-slate-500">Acceso privado</span>
            </div>
            <input
                id="password"
                name="password"
                type="password"
                autocomplete="current-password"
                required
                class="mt-2 block w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-slate-950 outline-none transition focus:border-kp-green-dark focus:ring-3 focus:ring-kp-green/15"
            >
            @error('password')
                <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-3 text-sm text-slate-600">
            <input name="remember" type="checkbox" value="1" class="size-4 rounded border-slate-300 text-kp-green-dark focus:ring-kp-green-dark">
            Mantener sesión iniciada en este equipo
        </label>

        <button type="submit" class="rounded-lg bg-kp-green-dark px-5 py-3 font-semibold text-white transition hover:bg-kp-green-deep focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2">
            Ingresar
        </button>
    </form>

    <p class="mt-8 border-t border-slate-200 pt-6 text-xs leading-5 text-slate-500">
        El acceso y las consultas realizadas en esta plataforma son auditados.
    </p>
@endsection
