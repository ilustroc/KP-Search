<section class="kp-section-card min-w-0">
    <x-ui.section-header title="Direcciones" icon="map-pin" :count="count($addresses)" />

    <div class="max-w-full overflow-x-auto">
        <table class="kp-data-table min-w-[780px]">
            <thead><tr><th>Dirección</th><th>Departamento</th><th>Provincia</th><th>Distrito</th><th>Procedencia</th></tr></thead>
            <tbody>
                @forelse ($addresses as $address)
                    <tr><td class="font-semibold text-slate-800">{{ data_get($address, 'direccion', '—') }}</td><td>{{ data_get($address, 'departamento', '—') }}</td><td>{{ data_get($address, 'provincia', '—') }}</td><td>{{ data_get($address, 'distrito', '—') }}</td><td>{{ data_get($address, 'data_procedencia', '—') }}</td></tr>
                @empty
                    <tr class="kp-data-table__empty"><td colspan="5" class="text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
