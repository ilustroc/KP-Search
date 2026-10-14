@php
    $states = [
        'loading' => [
            'eyebrow' => 'Cargando',
            'title' => 'Consultando información',
            'description' => 'Espere un momento mientras preparamos el módulo solicitado.',
            'tone' => 'border-slate-200 bg-white',
            'dot' => 'bg-kp-green animate-pulse',
        ],
        'empty' => [
            'eyebrow' => 'Sin registros',
            'title' => 'No se encontró información',
            'description' => 'No existen datos disponibles para este módulo y documento.',
            'tone' => 'border-slate-200 bg-white',
            'dot' => 'bg-slate-400',
        ],
        'error' => [
            'eyebrow' => 'Error',
            'title' => 'No fue posible cargar el módulo',
            'description' => 'Intente nuevamente. Si el problema continúa, comuníquese con Sistemas.',
            'tone' => 'border-red-200 bg-red-50',
            'dot' => 'bg-red-600',
        ],
        'forbidden' => [
            'eyebrow' => 'Acceso restringido',
            'title' => 'No tiene permiso para ver este módulo',
            'description' => 'Solicite acceso a un administrador si necesita consultar esta información.',
            'tone' => 'border-amber-200 bg-amber-50',
            'dot' => 'bg-amber-600',
        ],
        'unavailable' => [
            'eyebrow' => 'No disponible',
            'title' => 'Módulo temporalmente no disponible',
            'description' => 'El contrato de datos Gold para este módulo aún no ha sido publicado.',
            'tone' => 'border-slate-200 bg-white',
            'dot' => 'bg-kp-blue',
        ],
    ];
    $content = $states[$state] ?? $states['error'];
@endphp

<div class="rounded-xl border p-6 sm:p-8 {{ $content['tone'] }}" data-module-state="{{ $state }}">
    <div class="flex items-start gap-4">
        <span class="mt-1.5 size-2.5 shrink-0 rounded-full {{ $content['dot'] }}" aria-hidden="true"></span>
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">{{ $content['eyebrow'] }}</p>
            <h2 class="mt-2 text-xl font-semibold text-slate-900">{{ $content['title'] }}</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">{{ $content['description'] }}</p>
            @isset($moduleLabel)
                <p class="mt-4 text-xs font-medium text-slate-500">Módulo: {{ $moduleLabel }}</p>
            @endisset
        </div>
    </div>
</div>
