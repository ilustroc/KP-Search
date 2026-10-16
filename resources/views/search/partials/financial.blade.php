<div class="grid min-w-0 gap-4">
    <section class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
        <div class="flex min-h-11 flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-4 py-2">
            <div class="flex min-w-0 items-center gap-2.5">
                <svg class="size-4.5 shrink-0 text-kp-green-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 19V9"></path><path d="M10 19V5"></path><path d="M16 19v-7"></path><path d="M22 19V3"></path><path d="M2 19h22"></path>
                </svg>
                <h2 class="truncate text-[15px] font-semibold text-slate-800">SBS Resumen</h2>
                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($summary) }}</span>
            </div>
            @if ($summary !== [])
                <span class="text-xs font-medium text-slate-500">Fecha reporte: <strong class="text-slate-700">{{ data_get($summary[0], 'fecha_reporte', 'No disponible') }}</strong></span>
            @endif
        </div>
        @if ($summary === [])
            <p class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</p>
        @else
            @php($report = $summary[0])
            <div class="grid gap-2.5 px-4 py-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (['normal' => 'Normal', 'cpp' => 'CPP', 'deficiente' => 'Deficiente', 'dudoso' => 'Dudoso', 'perdida' => 'Pérdida'] as $key => $label)
                    @php($percentage = (float) data_get($report, $key, 0))
                    <div class="rounded-md border border-slate-200 bg-slate-50/50 px-3 py-2.5">
                        <div class="flex items-center justify-between gap-2 text-xs"><span class="font-semibold text-slate-600">{{ $label }}</span><strong class="text-slate-900">{{ number_format($percentage, 1) }}%</strong></div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200"><div class="h-full rounded-full bg-kp-green" style="width: {{ min(max($percentage, 0), 100) }}%"></div></div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
        <div class="flex h-11 items-center justify-between gap-3 border-b border-slate-100 px-4">
            <div class="flex min-w-0 items-center gap-2.5">
                <svg class="size-4.5 shrink-0 text-kp-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M3 10h18"></path><path d="M7 15h3"></path>
                </svg>
                <h2 class="truncate text-[15px] font-semibold text-slate-800">Cuentas por entidad</h2>
            </div>
            <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($accounts) }}</span>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-[13px]">
                <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-[0.05em] text-slate-500"><tr><th class="px-4 py-2 font-semibold">Código SBS</th><th class="px-4 py-2 font-semibold">Entidad</th><th class="px-4 py-2 font-semibold">Condición</th><th class="px-4 py-2 font-semibold">Tipo crédito</th><th class="px-4 py-2 font-semibold">Monto</th><th class="px-4 py-2 font-semibold">Días atraso</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-slate-700" data-expandable-table>
                    @forelse ($accounts as $account)
                        <tr @if ($loop->index >= 5) data-expandable-row hidden @endif><td class="px-4 py-2.5 font-medium">{{ data_get($account, 'codigo_sbs', '—') }}</td><td class="px-4 py-2.5">{{ data_get($account, 'entidad', '—') }}</td><td class="px-4 py-2.5">{{ data_get($account, 'condicion', '—') }}</td><td class="px-4 py-2.5">{{ data_get($account, 'tipo_credito', '—') }}</td><td class="px-4 py-2.5">{{ data_get($account, 'monto', '—') }}</td><td class="px-4 py-2.5">{{ data_get($account, 'dias_atraso', '—') }}</td></tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (count($accounts) > 5)
            <div class="border-t border-slate-100 px-4 py-2.5">
                <button type="button" data-table-toggle aria-expanded="false" class="text-sm font-semibold text-kp-green-dark hover:text-kp-green-deep focus:outline-none focus:underline">Ver más</button>
            </div>
        @endif
    </section>

    <section class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
        <div class="flex h-11 items-center justify-between gap-3 border-b border-slate-100 px-4">
            <div class="flex min-w-0 items-center gap-2.5">
                <svg class="size-4.5 shrink-0 text-kp-green-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 21V7l8-4 8 4v14"></path><path d="M9 21v-5h6v5"></path><path d="M9 9h1"></path><path d="M14 9h1"></path><path d="M9 12h1"></path><path d="M14 12h1"></path>
                </svg>
                <h2 class="truncate text-[15px] font-semibold text-slate-800">Essalud</h2>
            </div>
            <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($essalud) }}</span>
        </div>
        <div class="max-w-full overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-[13px]">
                <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-[0.05em] text-slate-500"><tr><th class="px-4 py-2 font-semibold">Periodo</th><th class="px-4 py-2 font-semibold">Empresa</th><th class="px-4 py-2 font-semibold">RUC</th><th class="px-4 py-2 font-semibold">Sueldo</th><th class="px-4 py-2 font-semibold">Situación</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($essalud as $employment)
                        <tr><td class="px-4 py-2.5 font-medium">{{ data_get($employment, 'periodo', '—') }}</td><td class="px-4 py-2.5">{{ data_get($employment, 'empresa', '—') }}</td><td class="px-4 py-2.5">{{ data_get($employment, 'ruc', '—') }}</td><td class="px-4 py-2.5">{{ data_get($employment, 'sueldo', '—') }}</td><td class="px-4 py-2.5">{{ data_get($employment, 'situacion', '—') }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
