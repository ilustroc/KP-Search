<?php

namespace Database\Seeders;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;

class LocalPreviewGoldSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local') || ! config('search.preview_enabled') || config('search.connection') !== 'sqlite_preview') {
            throw new LogicException('La previsualización solo está disponible en desarrollo local.');
        }

        $connection = 'sqlite_preview';

        foreach ([
            'search_laboral_essalud',
            'search_sbs_detalle',
            'search_sbs_resumen',
            'search_familiares',
            'search_propiedades',
            'search_vehiculos',
            'search_direcciones',
            'search_correos',
            'search_telefonos',
            'search_person_index',
        ] as $table) {
            Schema::connection($connection)->dropIfExists($table);
        }

        Schema::connection($connection)->create('search_person_index', function (Blueprint $table): void {
            $table->string('documento')->primary();
            $table->string('tipo_documento');
            $table->string('nombre_completo')->nullable();
            $table->string('paterno')->nullable();
            $table->string('materno')->nullable();
            $table->string('nombres')->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo')->nullable();
            $table->string('estado_civil')->nullable();
            $table->text('direccion_origen')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
        Schema::connection($connection)->create('search_telefonos', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('telefono');
            $table->string('operador')->nullable();
            $table->date('fecha_data')->nullable();
        });
        Schema::connection($connection)->create('search_correos', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('correo');
        });
        Schema::connection($connection)->create('search_direcciones', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('direccion')->nullable();
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('data_procedencia')->nullable();
            $table->unsignedInteger('procedencia_anio')->nullable();
        });
        Schema::connection($connection)->create('search_vehiculos', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('placa')->nullable();
            $table->string('marca')->nullable();
            $table->string('clase')->nullable();
            $table->unsignedInteger('anio_compra')->nullable();
            $table->unsignedInteger('anio_fabricacion')->nullable();
            $table->unsignedInteger('numero_transferencias')->nullable();
        });
        Schema::connection($connection)->create('search_propiedades', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('partida_registral')->nullable();
            $table->string('zona_registral')->nullable();
            $table->string('oficina')->nullable();
        });
        Schema::connection($connection)->create('search_familiares', function (Blueprint $table): void {
            $table->unsignedBigInteger('gold_family_id');
            $table->string('documento');
            $table->string('documento_familiar')->nullable();
            $table->string('paterno_familiar')->nullable();
            $table->string('materno_familiar')->nullable();
            $table->string('nombres_familiar')->nullable();
            $table->string('nacimiento_familiar')->nullable();
            $table->string('tipo_familiar')->nullable();
            $table->unsignedBigInteger('source_row_number')->nullable();
        });
        Schema::connection($connection)->create('search_sbs_resumen', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('cod_sbs');
            $table->string('fecha_reporte_sbs');
            $table->unsignedInteger('cantidad_empresas')->nullable();
            $table->decimal('calificacion_normal', 8, 4)->nullable();
            $table->decimal('calificacion_cpp', 8, 4)->nullable();
            $table->decimal('calificacion_deficiente', 8, 4)->nullable();
            $table->decimal('calificacion_dudoso', 8, 4)->nullable();
            $table->decimal('calificacion_perdida', 8, 4)->nullable();
        });
        Schema::connection($connection)->create('search_sbs_detalle', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('fecha_reporte_sbs');
            $table->string('cod_sbs')->nullable();
            $table->string('entidad')->nullable();
            $table->string('tipo_credito')->nullable();
            $table->string('condicion')->nullable();
            $table->decimal('monto', 16, 4)->nullable();
            $table->unsignedInteger('dias_atraso')->nullable();
        });
        Schema::connection($connection)->create('search_laboral_essalud', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('periodo');
            $table->string('ruc')->nullable();
            $table->string('nombre_empresa')->nullable();
            $table->decimal('sueldo', 16, 4)->nullable();
            $table->string('situacion')->nullable();
        });

        $this->insertPreviewData($connection);
    }

    private function insertPreviewData(string $connection): void
    {
        DB::connection($connection)->table('search_person_index')->insert([
            'documento' => '12345678', 'tipo_documento' => 'DNI', 'nombre_completo' => 'MARÍA ELENA TORRES SALAZAR',
            'paterno' => 'TORRES', 'materno' => 'SALAZAR', 'nombres' => 'MARÍA ELENA', 'fecha_nacimiento' => '1988-06-14',
            'sexo' => '2', 'estado_civil' => 'CASADO', 'direccion_origen' => 'LIMA-LIMA-MIRAFLORES',
            'source_code' => 'LOCAL', 'batch_id' => 1, 'updated_at' => '2026-10-19 09:00:00',
        ]);
        DB::connection($connection)->table('search_telefonos')->insert([
            ['documento' => '12345678', 'telefono' => '987654321', 'operador' => 'CLARO', 'fecha_data' => '2026-09-14'],
            ['documento' => '12345678', 'telefono' => '956123456', 'operador' => 'ENTEL', 'fecha_data' => '2026-05-20'],
        ]);
        DB::connection($connection)->table('search_correos')->insert([
            ['documento' => '12345678', 'correo' => 'maria.torres@example.test'],
            ['documento' => '12345678', 'correo' => 'mtorres@example.test'],
        ]);
        DB::connection($connection)->table('search_direcciones')->insert([
            ['documento' => '12345678', 'direccion' => 'AV. PARDO 420', 'departamento' => 'LIMA', 'provincia' => 'LIMA', 'distrito' => 'MIRAFLORES', 'data_procedencia' => 'RENIEC 2026', 'procedencia_anio' => 2026],
            ['documento' => '12345678', 'direccion' => 'CALLE LAS DALIAS 118', 'departamento' => 'LIMA', 'provincia' => 'LIMA', 'distrito' => 'SURCO', 'data_procedencia' => 'RENIEC 2024', 'procedencia_anio' => 2024],
        ]);
        DB::connection($connection)->table('search_vehiculos')->insert([
            ['documento' => '12345678', 'placa' => 'B7A-321', 'marca' => 'TOYOTA', 'clase' => 'AUTOMÓVIL', 'anio_compra' => 2022, 'anio_fabricacion' => 2021, 'numero_transferencias' => 1],
        ]);
        DB::connection($connection)->table('search_propiedades')->insert([
            ['documento' => '12345678', 'partida_registral' => 'P02086269', 'zona_registral' => 'IX', 'oficina' => 'LIMA'],
        ]);
        DB::connection($connection)->table('search_familiares')->insert([
            ['gold_family_id' => 1, 'documento' => '12345678', 'documento_familiar' => '87654321', 'paterno_familiar' => 'TORRES', 'materno_familiar' => 'SALAZAR', 'nombres_familiar' => 'LUIS ALBERTO', 'nacimiento_familiar' => '1992-09-03', 'tipo_familiar' => 'HERMANO', 'source_row_number' => 1],
            ['gold_family_id' => 2, 'documento' => '12345678', 'documento_familiar' => '00000000', 'paterno_familiar' => 'TORRES', 'materno_familiar' => 'SALAZAR', 'nombres_familiar' => 'ROSA ELENA', 'nacimiento_familiar' => '1961-02-18', 'tipo_familiar' => 'M', 'source_row_number' => 2],
        ]);
        DB::connection($connection)->table('search_sbs_resumen')->insert([
            'documento' => '12345678', 'cod_sbs' => '001', 'fecha_reporte_sbs' => '202609', 'cantidad_empresas' => 2,
            'calificacion_normal' => 92.45, 'calificacion_cpp' => 7.55, 'calificacion_deficiente' => 0, 'calificacion_dudoso' => 0, 'calificacion_perdida' => 0,
        ]);
        DB::connection($connection)->table('search_sbs_detalle')->insert([
            ['documento' => '12345678', 'fecha_reporte_sbs' => '202609', 'cod_sbs' => '001', 'entidad' => 'BANCO DE PRUEBA', 'tipo_credito' => 'CONSUMO', 'condicion' => 'NOR', 'monto' => 12450.50, 'dias_atraso' => 0],
            ['documento' => '12345678', 'fecha_reporte_sbs' => '202609', 'cod_sbs' => '002', 'entidad' => 'FINANCIERA MODELO', 'tipo_credito' => 'VEHICULAR', 'condicion' => 'CPP', 'monto' => 8800.00, 'dias_atraso' => 7],
        ]);
        DB::connection($connection)->table('search_laboral_essalud')->insert([
            ['documento' => '12345678', 'periodo' => '202609', 'ruc' => '20100000001', 'nombre_empresa' => 'EMPRESA DEMO S.A.C.', 'sueldo' => 5200.00, 'situacion' => 'ACTIVO'],
            ['documento' => '12345678', 'periodo' => '202507', 'ruc' => '20400000002', 'nombre_empresa' => 'SERVICIOS EJEMPLO S.R.L.', 'sueldo' => 4300.00, 'situacion' => 'ACTIVO'],
        ]);
    }
}
