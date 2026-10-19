<section class="kp-section-card min-w-0">
    <x-ui.section-header title="Propiedades" icon="home" :count="count($properties)" />

    <div class="max-w-full overflow-x-auto">
        <table class="kp-data-table min-w-[560px]">
            <thead><tr><th>Partida registral</th><th>Zona registral</th><th>Oficina</th></tr></thead>
            <tbody>
                @forelse ($properties as $property)
                    <tr><td class="font-semibold text-slate-800">{{ data_get($property, 'partida_registral', '—') }}</td><td>{{ data_get($property, 'zona_registral', '—') }}</td><td>{{ data_get($property, 'oficina', '—') }}</td></tr>
                @empty
                    <tr class="kp-data-table__empty"><td colspan="3" class="text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
