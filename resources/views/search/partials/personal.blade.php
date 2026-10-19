<section class="kp-section-card">
    <x-ui.section-header title="Datos personales" icon="user" />

    @if ($person === null)
        <x-ui.empty-state />
    @else
        @php($sexo = mb_strtoupper(trim((string) ($person['sexo'] ?? ''))))
        @php($sexoLabel = match ($sexo) { 'MASCULINO' => 'Masculino', 'FEMENINO' => 'Femenino', default => 'No especificado' })
        @php($estadoCivil = $person['estado_civil'] ?? null)

        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 p-4 sm:grid-cols-2">
            <x-ui.info-field label="Sexo" :value="$sexoLabel" icon="user" />
            <x-ui.info-field label="Estado civil" :value="$estadoCivil" icon="heart" />
            <x-ui.info-field label="Fecha de nacimiento" :value="$person['fecha_nacimiento_formateada'] ?? null" icon="calendar" />
            <x-ui.info-field label="Edad" :value="isset($person['edad']) ? $person['edad'].' años' : null" icon="clock" />
            <x-ui.info-field label="Dirección de origen" :value="$person['direccion_origen'] ?? null" icon="map-pin" wide />
            <x-ui.info-field label="Padre" :value="$person['padre'] ?? null" icon="user" />
            <x-ui.info-field label="Madre" :value="$person['madre'] ?? null" icon="user" />
        </dl>
    @endif
</section>
