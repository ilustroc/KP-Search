<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
    <div class="flex h-11 items-center justify-between gap-3 border-b border-slate-100 px-4">
        <div class="flex min-w-0 items-center gap-2.5">
            <svg class="size-4.5 shrink-0 text-kp-blue" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                <path d="m4 7 8 6 8-6"></path>
            </svg>
            <h2 class="truncate text-[15px] font-semibold text-slate-800">Correos</h2>
        </div>
        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-600">{{ count($emails) }}</span>
    </div>
    @if ($emails === [])
        <p class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</p>
    @else
        <ul class="divide-y divide-slate-100">
            @foreach ($emails as $email)
                <li class="break-all px-4 py-2.5 text-sm text-slate-700">{{ data_get($email, 'correo', 'No disponible') }}</li>
            @endforeach
        </ul>
    @endif
</section>
