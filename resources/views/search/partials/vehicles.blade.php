<section class="kp-section-card min-w-0">
    <x-ui.section-header title="Vehículos" icon="car" :count="count($vehicles)" />

    <div class="max-w-full overflow-x-auto">
        <table class="kp-data-table min-w-[720px]">
            <thead><tr><th>Placa</th><th>Marca</th><th>Clase</th><th>Año</th><th>Transferencias</th></tr></thead>
            <tbody>
                @forelse ($vehicles as $vehicle)
                    @php($vehicleYear = filled(data_get($vehicle, 'anio_fabricacion')) ? data_get($vehicle, 'anio_fabricacion') : data_get($vehicle, 'anio_compra'))
                    <tr><td class="font-semibold text-slate-800">{{ filled(data_get($vehicle, 'placa')) ? data_get($vehicle, 'placa') : 'Sin placa' }}</td><td>{{ data_get($vehicle, 'marca', '—') }}</td><td>{{ data_get($vehicle, 'clase', '—') }}</td><td>{{ filled($vehicleYear) ? $vehicleYear : '—' }}</td><td>{{ filled(data_get($vehicle, 'numero_transferencias')) ? data_get($vehicle, 'numero_transferencias') : '—' }}</td></tr>
                @empty
                    <tr class="kp-data-table__empty"><td colspan="5" class="text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
