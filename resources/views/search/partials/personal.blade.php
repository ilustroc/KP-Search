<section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-[0_1px_5px_rgba(15,23,42,0.04)]">
    <div class="flex h-11 items-center gap-2.5 border-b border-slate-100 px-4">
        <svg class="size-4.5 shrink-0 text-kp-green-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="8" r="3.5"></circle>
            <path d="M5 20a7 7 0 0 1 14 0"></path>
        </svg>
        <h2 class="text-[15px] font-semibold text-slate-800">Datos personales</h2>
    </div>
    @if ($person === null)
        <p class="px-4 py-3.5 text-sm text-slate-500">No se encontraron datos</p>
    @else
        <dl class="grid grid-cols-2 gap-x-5 gap-y-4 px-4 py-4">
            @foreach ([
                'Sexo' => $person['sexo'] ?? null,
                'Estado civil' => $person['estado_civil'] ?? null,
                'Fecha nacimiento' => $person['fecha_nacimiento_formateada'] ?? null,
                'Edad' => isset($person['edad']) ? $person['edad'].' años' : null,
                'Dirección origen' => $person['direccion_origen'] ?? null,
                'Padre' => $person['padre'] ?? null,
                'Madre' => $person['madre'] ?? null,
            ] as $label => $value)
                <div @class(['col-span-2' => $label === 'Dirección origen'])>
                    <dt class="text-[11px] font-semibold uppercase tracking-[0.06em] text-slate-400">{{ $label }}</dt>
                    <dd class="mt-1 break-words text-sm font-semibold leading-5 text-slate-800">{{ filled($value) ? $value : '—' }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</section>
