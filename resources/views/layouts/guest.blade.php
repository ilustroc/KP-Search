<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Acceso | KP SEARCH')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="grid min-h-screen lg:grid-cols-[minmax(0,1.1fr)_minmax(420px,0.9fr)]">
        <section class="hidden bg-kp-blue px-16 py-14 text-white lg:flex lg:flex-col lg:justify-between">
            <img
                src="{{ asset('brand/kp-invest-logo.png') }}"
                alt="KP Invest"
                width="210"
                height="50"
                class="h-12 w-auto rounded-sm bg-white px-3 py-2"
            >
            <div class="max-w-xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-300">Información empresarial</p>
                <h1 class="mt-5 text-5xl font-semibold leading-tight">Consulta segura, centralizada y auditable.</h1>
                <p class="mt-6 max-w-lg text-lg leading-8 text-blue-100">Acceso exclusivo para personal autorizado de KP INVEST.</p>
            </div>
            <p class="text-sm text-blue-200">KP SEARCH · Plataforma interna</p>
        </section>

        <section class="flex items-center justify-center px-6 py-12 sm:px-12">
            <div class="w-full max-w-md">
                <img
                    src="{{ asset('brand/kp-invest-logo.png') }}"
                    alt="KP Invest"
                    width="210"
                    height="50"
                    class="mb-10 h-12 w-auto lg:hidden"
                >
                @yield('content')
            </div>
        </section>
    </main>
</body>
</html>
