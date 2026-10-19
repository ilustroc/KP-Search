<section class="kp-section-card min-w-0">
    <x-ui.section-header title="Familiares" icon="users" :count="count($family)" />

    <div class="max-w-full overflow-x-auto">
        <table class="kp-data-table min-w-[680px]">
            <thead><tr><th>Documento familiar</th><th>Apellidos y nombres</th><th>Fecha de nacimiento</th><th>Tipo de familiar</th></tr></thead>
            <tbody>
                @forelse ($family as $relative)
                    <tr><td class="font-semibold text-slate-800">{{ data_get($relative, 'documento_familiar') ?: '—' }}</td><td>{{ data_get($relative, 'nombre_completo') ?: '—' }}</td><td class="whitespace-nowrap">{{ data_get($relative, 'nacimiento_familiar_formateado') ?: '—' }}</td><td>{{ data_get($relative, 'tipo_familiar') ?: '—' }}</td></tr>
                @empty
                    <tr class="kp-data-table__empty"><td colspan="4" class="text-sm text-slate-500">No se encontraron datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
