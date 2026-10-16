<section class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
    <div class="flex h-11 items-center justify-between gap-3 border-b border-slate-100 px-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <svg class="size-4.5 shrink-0 text-kp-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"></path>
                <circle cx="12" cy="10" r="2.5"></circle>
            </svg>
            <h2 class="truncate text-[15px] font-semibold text-slate-800">Direcciones</h2>
        </div>
        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($addresses) }}</span>
    </div>
    <div class="max-w-full overflow-x-auto">
        <table class="w-full min-w-[780px] text-left text-[13px]">
            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-[0.05em] text-slate-500">
                <tr><th class="px-4 py-2 font-semibold">Dirección</th><th class="px-4 py-2 font-semibold">Departamento</th><th class="px-4 py-2 font-semibold">Provincia</th><th class="px-4 py-2 font-semibold">Distrito</th><th class="px-4 py-2 font-semibold">Procedencia</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($addresses as $address)
                    <tr><td class="px-4 py-2.5 font-medium">{{ data_get($address, 'direccion', '—') }}</td><td class="px-4 py-2.5">{{ data_get($address, 'departamento', '—') }}</td><td class="px-4 py-2.5">{{ data_get($address, 'provincia', '—') }}</td><td class="px-4 py-2.5">{{ data_get($address, 'distrito', '—') }}</td><td class="px-4 py-2.5">{{ data_get($address, 'procedencia', '—') }}</td></tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
