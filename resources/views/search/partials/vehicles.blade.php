<section class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
    <div class="flex h-11 items-center justify-between gap-3 border-b border-slate-100 px-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <svg class="size-4.5 shrink-0 text-kp-green-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="m5 16 1-5 2-3h8l2 3 1 5"></path>
                <path d="M3 16h18v3H3z"></path>
                <circle cx="6" cy="19" r="1.5"></circle>
                <circle cx="18" cy="19" r="1.5"></circle>
            </svg>
            <h2 class="truncate text-[15px] font-semibold text-slate-800">Vehículos</h2>
        </div>
        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($vehicles) }}</span>
    </div>
    <div class="max-w-full overflow-x-auto">
        <table class="w-full min-w-[720px] text-left text-[13px]">
            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-[0.05em] text-slate-500"><tr><th class="px-4 py-2 font-semibold">Placa</th><th class="px-4 py-2 font-semibold">Marca</th><th class="px-4 py-2 font-semibold">Clase</th><th class="px-4 py-2 font-semibold">Año</th><th class="px-4 py-2 font-semibold">Transferencias</th></tr></thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($vehicles as $vehicle)
                    @php($vehicleYear = filled(data_get($vehicle, 'anio_fabricacion')) ? data_get($vehicle, 'anio_fabricacion') : data_get($vehicle, 'anio_compra'))
                    <tr><td class="px-4 py-2.5 font-medium">{{ filled(data_get($vehicle, 'placa')) ? data_get($vehicle, 'placa') : 'Sin placa' }}</td><td class="px-4 py-2.5">{{ data_get($vehicle, 'marca', '—') }}</td><td class="px-4 py-2.5">{{ data_get($vehicle, 'clase', '—') }}</td><td class="px-4 py-2.5">{{ filled($vehicleYear) ? $vehicleYear : '—' }}</td><td class="px-4 py-2.5">{{ filled(data_get($vehicle, 'numero_transferencias')) ? data_get($vehicle, 'numero_transferencias') : '—' }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
