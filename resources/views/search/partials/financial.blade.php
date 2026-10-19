<div class="grid min-w-0 gap-4">
    <section class="kp-section-card min-w-0">
        <x-ui.section-header title="Resumen SBS" icon="chart" :count="count($summary)">
            @if ($summary !== [])
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600"><x-ui.icon name="calendar" size="sm" class="text-slate-400" />Último reporte: {{ data_get($summary[0], 'fecha_reporte_formateada') ?: '—' }}</span>
            @endif
        </x-ui.section-header>

        @if ($summary === [])
            <x-ui.empty-state />
        @else
            @php($report = $summary[0])
            @php($historicalReports = array_values(array_filter($summary, static fn (array $summaryRecord): bool => data_get($summaryRecord, 'fecha_reporte_sbs') !== data_get($report, 'fecha_reporte_sbs'))))
            <div class="p-4">
                <p class="mb-4 text-sm text-slate-600">Empresas reportantes: <strong class="font-semibold text-slate-800">{{ data_get($report, 'cantidad_empresas') ?? '—' }}</strong></p>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach (['normal' => 'Normal', 'cpp' => 'CPP', 'deficiente' => 'Deficiente', 'dudoso' => 'Dudoso', 'perdida' => 'Pérdida'] as $key => $label)
                        @php($percentage = min(100, max(0, (float) data_get($report, "calificacion_{$key}", 0))))
                        @php($progress = number_format($percentage, 2, '.', ''))
                        @php($formattedValue = data_get($report, "calificacion_{$key}_formateada") ?: '—')
                        <x-ui.financial-indicator :label="$label" :value="$formattedValue" :percentage="$progress" :tone="$key" />
                    @endforeach
                </div>
            </div>

            @if ($historicalReports !== [])
                <details class="border-t border-slate-100 px-4 py-3">
                    <summary class="cursor-pointer text-sm font-semibold text-kp-green-dark focus:outline-none">Ver historial de reportes ({{ count($historicalReports) }})</summary>
                    <div class="mt-3 max-w-full overflow-x-auto rounded-md border border-slate-100">
                        <table class="kp-data-table min-w-[600px]">
                            <thead><tr><th>Periodo</th><th>Empresas reportantes</th><th>Normal</th><th>CPP</th><th>Pérdida</th></tr></thead>
                            <tbody>
                                @foreach ($historicalReports as $historicalReport)
                                    <tr><td class="font-semibold text-slate-800">{{ data_get($historicalReport, 'fecha_reporte_formateada') ?: '—' }}</td><td>{{ data_get($historicalReport, 'cantidad_empresas') ?? '—' }}</td><td>{{ data_get($historicalReport, 'calificacion_normal_formateada') ?: '—' }}</td><td>{{ data_get($historicalReport, 'calificacion_cpp_formateada') ?: '—' }}</td><td>{{ data_get($historicalReport, 'calificacion_perdida_formateada') ?: '—' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif
        @endif
    </section>

    <section class="kp-section-card min-w-0">
        <x-ui.section-header title="Detalle SBS" icon="id-card" :count="count($accounts)" />

        @if ($accounts === [])
            <x-ui.empty-state />
        @else
            @foreach (collect($accounts)->groupBy('fecha_reporte_sbs') as $accountsInPeriod)
                <div class="border-b border-slate-100 last:border-b-0">
                    <h3 class="flex items-center gap-2 bg-slate-50 px-4 py-2.5 text-sm font-semibold text-slate-700"><x-ui.icon name="calendar" size="sm" class="text-slate-400" />Periodo {{ data_get($accountsInPeriod->first(), 'fecha_reporte_formateada') ?: '—' }}</h3>
                    <div class="max-w-full overflow-x-auto">
                        <table class="kp-data-table min-w-[760px]">
                            <thead><tr><th>Entidad</th><th>Tipo de crédito</th><th>Condición</th><th class="text-right">Monto</th><th>Días de atraso</th></tr></thead>
                            <tbody>
                                @foreach ($accountsInPeriod as $account)
                                    <tr><td class="font-semibold text-slate-800">{{ data_get($account, 'entidad') ?: '—' }}</td><td>{{ data_get($account, 'tipo_credito') ?: '—' }}</td><td><span class="kp-condition">{{ data_get($account, 'condicion_formateada') ?: '—' }}</span></td><td class="text-right font-medium tabular-nums">{{ data_get($account, 'monto_formateado') ?: '—' }}</td><td>{{ data_get($account, 'dias_atraso_formateados') ?: '—' }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    </section>

    <section class="kp-section-card min-w-0">
        <x-ui.section-header title="Historial laboral" icon="briefcase" :count="count($essalud)" />

        <div class="max-w-full overflow-x-auto">
            <table class="kp-data-table min-w-[760px]">
                <thead><tr><th>Periodo</th><th>Empresa</th><th>RUC</th><th class="text-right">Sueldo</th><th>Situación</th></tr></thead>
                <tbody>
                    @forelse ($essalud as $employment)
                        <tr><td class="whitespace-nowrap font-semibold text-slate-800">{{ data_get($employment, 'periodo_formateado') ?: '—' }}</td><td class="font-medium">{{ data_get($employment, 'empresa_formateada') ?: 'Empresa no informada' }}</td><td class="whitespace-nowrap">{{ data_get($employment, 'ruc') ?: '—' }}</td><td class="text-right font-medium tabular-nums">{{ data_get($employment, 'sueldo_formateado') ?: '—' }}</td><td>{{ data_get($employment, 'situacion') ?: '—' }}</td></tr>
                    @empty
                        <tr class="kp-data-table__empty"><td colspan="5" class="text-sm text-slate-500">No se encontraron datos</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
