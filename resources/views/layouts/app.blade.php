<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'KP SEARCH')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex min-h-20 max-w-7xl items-center justify-between gap-6 px-6 py-4 lg:px-8">
            <a href="{{ route('search.index') }}" class="flex items-center gap-5" aria-label="Ir al buscador">
                <img
                    src="{{ asset('brand/kp-invest-logo.png') }}"
                    alt="KP Invest"
                    width="210"
                    height="50"
                    class="h-10 w-auto"
                >
                <span class="hidden border-l border-slate-200 pl-5 text-sm font-semibold tracking-[0.14em] text-kp-blue sm:block">
                    KP SEARCH
                </span>
            </a>

            <div class="flex items-center gap-4">
                <div class="hidden text-right sm:block">
                    <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500">Acceso interno</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-2">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-7xl px-6 py-10 lg:px-8 lg:py-14">
        @yield('content')
    </main>

    <footer class="mx-auto max-w-7xl px-6 pb-8 text-xs text-slate-500 lg:px-8">
        Uso interno de KP INVEST. Toda consulta queda registrada.
    </footer>
</body>
</html>
