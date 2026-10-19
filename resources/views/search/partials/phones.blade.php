<section class="kp-section-card min-w-0">
    <x-ui.section-header title="Teléfonos" icon="phone" :count="count($phones)" />

    <div class="max-w-full overflow-x-auto">
        <table class="kp-data-table min-w-[580px]">
            <thead><tr><th>TELÉFONO</th><th>OPERADOR</th><th>FECHA DATA</th></tr></thead>
            <tbody>
                @forelse ($phones as $phone)
                    @php($operator = mb_strtoupper(trim((string) data_get($phone, 'operador', ''))))
                    <tr>
                        <td class="font-semibold text-slate-800">
                            <span class="inline-flex items-center gap-1.5">
                                {{ data_get($phone, 'telefono', '—') }}
                                <x-ui.whatsapp-link :phone="data_get($phone, 'telefono')" />
                            </span>
                        </td>
                        <td class="font-semibold text-slate-700">{{ $operator !== '' ? $operator : '—' }}</td>
                        <td class="whitespace-nowrap text-slate-600">{{ data_get($phone, 'fecha_data_formateada', '—') }}</td>
                    </tr>
                @empty
                    <tr class="kp-data-table__empty"><td colspan="3" class="text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
