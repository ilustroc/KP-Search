<section class="min-w-0 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
    <div class="flex h-11 items-center justify-between gap-3 border-b border-slate-100 px-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <svg class="size-4.5 shrink-0 text-kp-green-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path d="M7 3h3l1.5 4-2 1.5a15 15 0 0 0 6 6L17 12.5l4 1.5v3a4 4 0 0 1-4 4C9.3 21 3 14.7 3 7a4 4 0 0 1 4-4Z"></path>
            </svg>
            <h2 class="truncate text-[15px] font-semibold text-slate-800">Teléfonos</h2>
        </div>
        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($phones) }}</span>
    </div>
    <div class="max-w-full overflow-x-auto">
        <table class="w-full min-w-[620px] text-left text-[13px]">
            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-[0.05em] text-slate-500">
                <tr><th class="px-4 py-2 font-semibold">Teléfono</th><th class="px-4 py-2 font-semibold">OPERADOR</th><th class="px-4 py-2 font-semibold">Fecha Data</th><th class="px-4 py-2 font-semibold">Acción</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @forelse ($phones as $phone)
                    @php($phoneNumber = preg_replace('/\D+/', '', (string) data_get($phone, 'telefono', '')))
                    <tr>
                        <td class="px-4 py-2.5 font-medium">{{ data_get($phone, 'telefono', '—') }}</td>
                        <td class="px-4 py-2.5">{{ data_get($phone, 'operador', '—') }}</td>
                        <td class="px-4 py-2.5">{{ data_get($phone, 'fecha_data_formateada', '—') }}</td>
                        <td class="px-4 py-2.5">
                            @if ($phoneNumber !== '')
                                <a href="https://api.whatsapp.com/send/?phone=51{{ $phoneNumber }}&text&type=phone_number&app_absent=0" target="_blank" rel="noopener noreferrer" class="inline-flex size-7 items-center justify-center rounded-md text-kp-green-dark transition hover:bg-kp-green/10 focus:outline-none focus:ring-2 focus:ring-kp-green-dark focus:ring-offset-1" aria-label="Abrir WhatsApp para {{ $phoneNumber }}">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path d="M20.5 11.5a8.5 8.5 0 0 1-12.6 7.4L3 20l1.2-4.5A8.5 8.5 0 1 1 20.5 11.5Z"></path>
                                        <path d="M8.5 7.5c.2-.5.4-.5.7-.5h.6c.2 0 .4.1.5.4l.7 1.7c.1.2.1.5 0 .7l-.5.6c-.1.2-.2.4 0 .6.5.9 1.2 1.6 2.1 2.1.2.1.4.1.6 0l.6-.5c.2-.2.5-.2.7 0l1.7.7c.3.1.4.3.4.5v.6c0 .3 0 .5-.5.7-.5.2-1.5.3-2.7-.2-1-.4-2.2-1.2-3.2-2.3s-1.8-2.2-2.3-3.2c-.5-1.2-.4-2.2-.2-2.7Z"></path>
                                    </svg>
                                </a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
