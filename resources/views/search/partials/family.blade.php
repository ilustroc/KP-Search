<section class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
    <div class="flex h-11 items-center justify-between gap-3 border-b border-slate-100 px-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <svg class="size-4.5 shrink-0 text-kp-green-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="9" cy="8" r="3"></circle>
                <circle cx="17" cy="9" r="2.5"></circle>
                <path d="M3 20a6 6 0 0 1 12 0"></path>
                <path d="M14 15.5A5 5 0 0 1 21 20"></path>
            </svg>
            <h2 class="truncate text-[15px] font-semibold text-slate-800">Familiares</h2>
        </div>
        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($family) }}</span>
    </div>
    <div class="max-w-full overflow-x-auto">
        <table class="w-full min-w-[680px] text-left text-[13px]">
            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-[0.05em] text-slate-500"><tr><th class="px-4 py-2 font-semibold">Documento familiar</th><th class="px-4 py-2 font-semibold">Apellidos y nombres</th><th class="px-4 py-2 font-semibold">Fecha de nacimiento</th><th class="px-4 py-2 font-semibold">Tipo de familiar</th></tr></thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($family as $relative)
                    <tr><td class="px-4 py-2.5 font-medium">{{ data_get($relative, 'documento_familiar') ?: '—' }}</td><td class="px-4 py-2.5">{{ data_get($relative, 'nombre_completo') ?: '—' }}</td><td class="px-4 py-2.5">{{ data_get($relative, 'nacimiento_familiar_formateado') ?: '—' }}</td><td class="px-4 py-2.5">{{ data_get($relative, 'tipo_familiar') ?: '—' }}</td></tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
