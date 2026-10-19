#!/usr/bin/env sh
set -eu

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

preview_schema_is_ready() {
    php -r '
        $database = getenv("SEARCH_PREVIEW_DATABASE");
        $schema = [
            "search_person_index" => ["documento", "tipo_documento", "nombre_completo", "paterno", "materno", "nombres", "fecha_nacimiento", "sexo", "estado_civil", "direccion_origen", "source_code", "batch_id", "updated_at"],
            "search_telefonos" => ["documento", "telefono", "operador", "fecha_data"],
            "search_correos" => ["documento", "correo"],
            "search_direcciones" => ["documento", "direccion", "departamento", "provincia", "distrito", "data_procedencia", "procedencia_anio"],
            "search_vehiculos" => ["documento", "placa", "marca", "clase", "anio_compra", "anio_fabricacion", "numero_transferencias"],
            "search_propiedades" => ["documento", "partida_registral", "zona_registral", "oficina"],
            "search_familiares" => ["gold_family_id", "documento", "documento_familiar", "paterno_familiar", "materno_familiar", "nombres_familiar", "nacimiento_familiar", "tipo_familiar", "source_row_number"],
            "search_sbs_resumen" => ["documento", "cod_sbs", "fecha_reporte_sbs", "cantidad_empresas", "calificacion_normal", "calificacion_cpp", "calificacion_deficiente", "calificacion_dudoso", "calificacion_perdida"],
            "search_sbs_detalle" => ["documento", "fecha_reporte_sbs", "cod_sbs", "entidad", "tipo_credito", "condicion", "monto", "dias_atraso"],
            "search_laboral_essalud" => ["documento", "periodo", "ruc", "nombre_empresa", "sueldo", "situacion"],
        ];

        if (! is_string($database) || ! is_file($database)) {
            exit(1);
        }

        $connection = new PDO("sqlite:".$database);
        foreach ($schema as $table => $requiredColumns) {
            $columns = $connection
                ->query("pragma table_info(".$connection->quote($table).")")
                ->fetchAll(PDO::FETCH_COLUMN, 1);

            if (array_diff($requiredColumns, $columns) !== []) {
                exit(1);
            }
        }
    '
}

if [ "${SEARCH_LOCAL_PREVIEW:-false}" = "true" ]; then
    mkdir -p database
    touch database/preview.sqlite

    if [ "${SEARCH_PREVIEW_RESET:-false}" = "true" ] || ! preview_schema_is_ready; then
        php artisan db:seed --class=LocalPreviewGoldSeeder --database=sqlite_preview --force
    else
        echo 'Preview SQLite schema is ready.'
    fi
fi

exec "$@"
