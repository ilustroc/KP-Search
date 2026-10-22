<?php

namespace Tests\Feature\Services;

use App\Services\SearchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    public function test_search_uses_exact_selects_for_all_published_datasets(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_person_index')->insert($this->personRecord());
        DB::connection('mysql_gold')->table('search_telefonos')->insert($this->phoneRecord());
        DB::connection('mysql_gold')->flushQueryLog();
        DB::connection('mysql_gold')->enableQueryLog();

        $result = app(SearchService::class)->search('12345678');
        $queries = DB::connection('mysql_gold')->getQueryLog();

        $this->assertSame('CLIENTE EXACTO', $result['person']['nombre_completo']);
        $this->assertSame('PADRE DE PRUEBA', $result['person']['padre']);
        $this->assertSame('MADRE DE PRUEBA', $result['person']['madre']);
        $this->assertCount(10, $queries);

        foreach ($queries as $query) {
            $this->assertStringContainsString('where "documento" = ?', $query['query']);
            $this->assertStringNotContainsString('like', mb_strtolower($query['query']));
            $this->assertSame(['12345678'], $query['bindings']);
        }
    }

    public function test_search_orders_phones_by_date_descending_then_phone_ascending(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_telefonos')->insert([
            $this->phoneRecord(['telefono' => '999999999', 'fecha_data' => '2025-10-02']),
            $this->phoneRecord(['telefono' => '888888888', 'fecha_data' => '2025-10-02']),
            $this->phoneRecord(['telefono' => '903211606', 'fecha_data' => '2026-07-01']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame([
            '903211606',
            '888888888',
            '999999999',
        ], array_column($result['phones'], 'telefono'));
        $this->assertSame('01/07/2026', $result['phones'][0]['fecha_data_formateada']);
    }

    public function test_search_orders_emails_ascending(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_correos')->insert([
            $this->emailRecord(['correo' => 'zeta@example.test']),
            $this->emailRecord(['correo' => 'alfa@example.test']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame(['alfa@example.test', 'zeta@example.test'], array_column($result['emails'], 'correo'));
    }

    public function test_search_orders_addresses_by_year_provenance_and_address(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_direcciones')->insert([
            $this->addressRecord(['direccion' => 'AV. BETA 100', 'procedencia_anio' => 2025, 'data_procedencia' => 'RENIEC 2025']),
            $this->addressRecord(['direccion' => 'AV. ALFA 100', 'procedencia_anio' => 2025, 'data_procedencia' => 'RENIEC 2025']),
            $this->addressRecord(['direccion' => 'JR. GAMMA 50', 'procedencia_anio' => 2022, 'data_procedencia' => 'RENIEC 2022']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame(['AV. ALFA 100', 'AV. BETA 100', 'JR. GAMMA 50'], array_column($result['addresses'], 'direccion'));
    }

    public function test_search_orders_vehicles_by_purchase_year_fabrication_year_and_plate(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_vehiculos')->insert([
            $this->vehicleRecord(['placa' => 'BBB-222', 'anio_compra' => 2020, 'anio_fabricacion' => 2020]),
            $this->vehicleRecord(['placa' => 'AAA-111', 'anio_compra' => 2020, 'anio_fabricacion' => 2020]),
            $this->vehicleRecord(['placa' => 'CCC-333', 'anio_compra' => 2022, 'anio_fabricacion' => 2019]),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame(['CCC-333', 'AAA-111', 'BBB-222'], array_column($result['vehicles'], 'placa'));
    }

    public function test_search_orders_properties_by_office_then_registry_number(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_propiedades')->insert([
            $this->propertyRecord(['oficina' => 'LIMA', 'partida_registral' => 'P02086269']),
            $this->propertyRecord(['oficina' => 'AREQUIPA', 'partida_registral' => '11184695']),
            $this->propertyRecord(['oficina' => 'LIMA', 'partida_registral' => '11184695']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame([
            'AREQUIPA:11184695',
            'LIMA:11184695',
            'LIMA:P02086269',
        ], array_map(
            static fn (array $property): string => $property['oficina'].':'.$property['partida_registral'],
            $result['properties'],
        ));
    }

    public function test_search_keeps_family_records_documents_relationship_codes_and_source_dates(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_familiares')->insert([
            $this->familyRecord(['gold_family_id' => 10, 'source_row_number' => 10, 'documento_familiar' => '9406251AN', 'paterno_familiar' => 'PEREZ', 'materno_familiar' => 'LOPEZ', 'nombres_familiar' => 'ANA', 'nacimiento_familiar' => '1980-01-15', 'tipo_familiar' => 'H']),
            $this->familyRecord(['gold_family_id' => 11, 'source_row_number' => 11, 'documento_familiar' => '9406251AN', 'paterno_familiar' => 'PEREZ', 'materno_familiar' => 'LOPEZ', 'nombres_familiar' => 'ANA', 'nacimiento_familiar' => '1980-01-15', 'tipo_familiar' => 'H']),
            $this->familyRecord(['gold_family_id' => 12, 'source_row_number' => 12, 'documento_familiar' => '00000000', 'nacimiento_familiar' => '2024-02-31', 'tipo_familiar' => 'C']),
            $this->familyRecord(['gold_family_id' => 13, 'source_row_number' => 13, 'documento_familiar' => '0', 'nacimiento_familiar' => 'SIN FECHA', 'tipo_familiar' => 'G']),
            $this->familyRecord(['gold_family_id' => 14, 'source_row_number' => 14, 'documento_familiar' => null, 'paterno_familiar' => null, 'materno_familiar' => null, 'nombres_familiar' => null, 'nacimiento_familiar' => null, 'tipo_familiar' => 'N']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertCount(5, $result['family']);
        $this->assertSame(['9406251AN', '9406251AN', '00000000', '0', null], array_column($result['family'], 'documento_familiar'));
        $this->assertSame(['H', 'H', 'C', 'G', 'N'], array_column($result['family'], 'tipo_familiar'));
        $this->assertSame('PEREZ LOPEZ ANA', $result['family'][0]['nombre_completo']);
        $this->assertSame($result['family'][0], $result['family'][1]);
        $this->assertSame('15/01/1980', $result['family'][0]['nacimiento_familiar_formateado']);
        $this->assertSame('2024-02-31', $result['family'][2]['nacimiento_familiar_formateado']);
        $this->assertSame('SIN FECHA', $result['family'][3]['nacimiento_familiar_formateado']);
        $this->assertNull($result['family'][4]['nombre_completo']);
        $this->assertNull($result['family'][4]['nacimiento_familiar_formateado']);
        $this->assertArrayNotHasKey('gold_family_id', $result['family'][0]);
        $this->assertArrayNotHasKey('source_code', $result['family'][0]);
    }

    public function test_unpublished_datasets_return_empty_collections(): void
    {
        $this->createGoldPersonIndex();

        $result = app(SearchService::class)->search('12345678');

        $this->assertNull($result['person']);
        $this->assertFalse($result['gold_query_failed']);
        $this->assertSame([], $result['phones']);
        $this->assertSame([], $result['addresses']);
        $this->assertSame([], $result['emails']);
        $this->assertSame([], $result['vehicles']);
        $this->assertSame([], $result['properties']);
        $this->assertSame([], $result['family']);
        $this->assertSame([], $result['sbs_summary']);
        $this->assertSame([], $result['sbs_accounts']);
        $this->assertSame([], $result['essalud']);
    }

    public function test_search_orders_sbs_summary_and_formats_percentages(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_sbs_resumen')->insert([
            $this->sbsSummaryRecord(['fecha_reporte_sbs' => '202607', 'cod_sbs' => '002', 'calificacion_normal' => '92.4500']),
            $this->sbsSummaryRecord(['fecha_reporte_sbs' => '202607', 'cod_sbs' => '001', 'calificacion_normal' => '100.0000']),
            $this->sbsSummaryRecord(['fecha_reporte_sbs' => '202506', 'cod_sbs' => '001', 'calificacion_normal' => '25.0000']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame(['001', '002', '001'], array_column($result['sbs_summary'], 'cod_sbs'));
        $this->assertSame('07/2026', $result['sbs_summary'][0]['fecha_reporte_formateada']);
        $this->assertSame('100%', $result['sbs_summary'][0]['calificacion_normal_formateada']);
        $this->assertSame('92.45%', $result['sbs_summary'][1]['calificacion_normal_formateada']);
    }

    public function test_search_orders_sbs_details_and_formats_conditions_and_nullable_fields(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_sbs_detalle')->insert([
            $this->sbsDetailRecord(['fecha_reporte_sbs' => '202607', 'entidad' => 'BANCO B', 'tipo_credito' => 'PERSONAL', 'condicion' => 'NOR', 'dias_atraso' => 0]),
            $this->sbsDetailRecord(['fecha_reporte_sbs' => '202607', 'entidad' => 'BANCO A', 'tipo_credito' => 'VEHICULAR', 'condicion' => 'CPP', 'dias_atraso' => 1]),
            $this->sbsDetailRecord(['fecha_reporte_sbs' => '202607', 'entidad' => 'BANCO A', 'tipo_credito' => null, 'condicion' => 'DEF', 'dias_atraso' => null]),
            $this->sbsDetailRecord(['fecha_reporte_sbs' => '202506', 'entidad' => 'BANCO Z', 'condicion' => 'DUD']),
            $this->sbsDetailRecord(['fecha_reporte_sbs' => '202506', 'entidad' => 'BANCO Y', 'condicion' => 'PER']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame(['BANCO A', 'BANCO A', 'BANCO B', 'BANCO Y', 'BANCO Z'], array_column($result['sbs_accounts'], 'entidad'));
        $this->assertNull($result['sbs_accounts'][0]['tipo_credito']);
        $this->assertNull($result['sbs_accounts'][0]['dias_atraso_formateados']);
        $this->assertSame(['Deficiente', 'Con problemas potenciales', 'Normal', 'Pérdida', 'Dudoso'], array_column($result['sbs_accounts'], 'condicion_formateada'));
        $this->assertSame('1 día', $result['sbs_accounts'][1]['dias_atraso_formateados']);
        $this->assertSame('0 días', $result['sbs_accounts'][2]['dias_atraso_formateados']);
    }

    public function test_search_orders_employment_and_formats_nullable_values(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_laboral_essalud')->insert([
            $this->employmentRecord(['periodo' => '202607', 'nombre_empresa' => 'ZETA S.A.C.', 'ruc' => '20100000002', 'sueldo' => '1250.5000', 'situacion' => 'ACTIVO']),
            $this->employmentRecord(['periodo' => '202607', 'nombre_empresa' => 'ALFA S.A.C.', 'ruc' => '20100000001', 'sueldo' => null, 'situacion' => null]),
            $this->employmentRecord(['periodo' => '202506', 'nombre_empresa' => null, 'ruc' => '20100000003']),
        ]);

        $result = app(SearchService::class)->search('12345678');

        $this->assertSame(['ALFA S.A.C.', 'ZETA S.A.C.', 'Empresa no informada'], array_column($result['essalud'], 'empresa_formateada'));
        $this->assertSame('07/2026', $result['essalud'][0]['periodo_formateado']);
        $this->assertNull($result['essalud'][0]['sueldo_formateado']);
        $this->assertSame('1,250.5', $result['essalud'][1]['sueldo_formateado']);
        $this->assertNull($result['essalud'][0]['situacion']);
    }

    public function test_gold_failure_does_not_log_document_or_sql(): void
    {
        $this->configureGoldConnection();
        Log::spy();

        $result = app(SearchService::class)->search('12345678');

        $this->assertNull($result['person']);
        $this->assertTrue($result['gold_query_failed']);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                $loggedData = $message.json_encode($context);

                return ! str_contains($loggedData, '12345678')
                    && ! str_contains(mb_strtolower($loggedData), 'select');
            });
    }

    public function test_financial_query_failure_does_not_log_document_sql_or_credentials(): void
    {
        $this->createGoldPersonIndex();
        config()->set('search.sbs_resumen.object', 'missing_financial_source');
        Log::spy();

        $result = app(SearchService::class)->search('12345678');

        $this->assertTrue($result['gold_query_failed']);
        Log::shouldHaveReceived('warning')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                $loggedData = $message.json_encode($context);

                return $context['dataset'] === 'SBS_RESUMEN'
                    && ! str_contains($loggedData, '12345678')
                    && ! str_contains(mb_strtolower($loggedData), 'select')
                    && ! str_contains(mb_strtolower($loggedData), 'password');
            });
    }

    private function configureGoldConnection(): void
    {
        config()->set('database.connections.mysql_gold', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge('mysql_gold');
    }

    private function createGoldPersonIndex(): void
    {
        $this->configureGoldConnection();

        Schema::connection('mysql_gold')->create('search_person_index', function (Blueprint $table): void {
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
            $table->string('padre')->nullable();
            $table->string('madre')->nullable();
            $table->string('source_code');
            $table->unsignedBigInteger('batch_id');
            $table->dateTime('updated_at');
        });

        Schema::connection('mysql_gold')->create('search_telefonos', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('telefono');
            $table->string('operador')->nullable();
            $table->date('fecha_data')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_correos', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('correo');
            $table->string('correo_original')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_direcciones', function (Blueprint $table): void {
            $table->string('address_id')->nullable();
            $table->string('documento');
            $table->string('direccion')->nullable();
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('data_procedencia')->nullable();
            $table->unsignedInteger('procedencia_anio')->nullable();
            $table->string('record_hash')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_vehiculos', function (Blueprint $table): void {
            $table->string('vehicle_id')->nullable();
            $table->string('documento');
            $table->string('nombre_completo_fuente')->nullable();
            $table->string('clase')->nullable();
            $table->unsignedInteger('anio_compra')->nullable();
            $table->unsignedInteger('anio_fabricacion')->nullable();
            $table->string('marca')->nullable();
            $table->string('placa')->nullable();
            $table->string('placa_original')->nullable();
            $table->unsignedInteger('numero_transferencias')->nullable();
            $table->string('record_hash')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_propiedades', function (Blueprint $table): void {
            $table->string('property_id')->nullable();
            $table->string('documento');
            $table->string('partida_registral')->nullable();
            $table->string('zona_registral')->nullable();
            $table->string('oficina')->nullable();
            $table->string('record_hash')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_familiares', function (Blueprint $table): void {
            $table->unsignedBigInteger('gold_family_id')->nullable();
            $table->string('documento');
            $table->string('documento_familiar')->nullable();
            $table->string('paterno_familiar')->nullable();
            $table->string('materno_familiar')->nullable();
            $table->string('nombres_familiar')->nullable();
            $table->string('nacimiento_familiar')->nullable();
            $table->string('tipo_familiar')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->unsignedBigInteger('source_row_number')->nullable();
            $table->string('record_hash')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_sbs_resumen', function (Blueprint $table): void {
            $table->string('documento');
            $table->string('cod_sbs');
            $table->string('fecha_reporte_sbs');
            $table->unsignedInteger('cantidad_empresas')->nullable();
            $table->decimal('calificacion_normal', 8, 4)->nullable();
            $table->decimal('calificacion_cpp', 8, 4)->nullable();
            $table->decimal('calificacion_deficiente', 8, 4)->nullable();
            $table->decimal('calificacion_dudoso', 8, 4)->nullable();
            $table->decimal('calificacion_perdida', 8, 4)->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_sbs_detalle', function (Blueprint $table): void {
            $table->string('gold_detail_id')->nullable();
            $table->string('fecha_reporte_sbs');
            $table->string('documento');
            $table->string('cod_sbs')->nullable();
            $table->string('entidad')->nullable();
            $table->string('tipo_credito')->nullable();
            $table->string('condicion')->nullable();
            $table->decimal('monto', 16, 4)->nullable();
            $table->unsignedInteger('dias_atraso')->nullable();
            $table->string('record_hash')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::connection('mysql_gold')->create('search_laboral_essalud', function (Blueprint $table): void {
            $table->string('gold_employment_id')->nullable();
            $table->string('documento');
            $table->string('periodo');
            $table->string('ruc')->nullable();
            $table->string('nombre_empresa')->nullable();
            $table->decimal('sueldo', 16, 4)->nullable();
            $table->string('situacion')->nullable();
            $table->string('record_hash')->nullable();
            $table->string('source_code')->nullable();
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function personRecord(): array
    {
        return [
            'documento' => '12345678',
            'tipo_documento' => 'DNI',
            'nombre_completo' => 'CLIENTE EXACTO',
            'paterno' => 'EXACTO',
            'materno' => null,
            'nombres' => 'CLIENTE',
            'fecha_nacimiento' => null,
            'sexo' => null,
            'estado_civil' => null,
            'direccion_origen' => null,
            'padre' => 'PADRE DE PRUEBA',
            'madre' => 'MADRE DE PRUEBA',
            'source_code' => 'TEST',
            'batch_id' => 1,
            'updated_at' => '2026-10-15 09:30:00',
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function phoneRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'telefono' => '903211606',
            'operador' => 'ENTEL',
            'fecha_data' => '2026-07-01',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function emailRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'correo' => 'cliente@example.test',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function addressRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'direccion' => 'JR. PRUEBA 100',
            'departamento' => 'LIMA',
            'provincia' => 'LIMA',
            'distrito' => 'COMAS',
            'data_procedencia' => 'RENIEC 2025',
            'procedencia_anio' => 2025,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function vehicleRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'placa' => 'ABC-123',
            'marca' => 'TOYOTA',
            'clase' => 'AUTOMOVIL',
            'anio_compra' => 2020,
            'anio_fabricacion' => 2019,
            'numero_transferencias' => 1,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function propertyRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'partida_registral' => 'P02086269',
            'zona_registral' => 'IX',
            'oficina' => 'LIMA',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function familyRecord(array $overrides = []): array
    {
        return array_merge([
            'gold_family_id' => 1,
            'documento' => '12345678',
            'documento_familiar' => '87654321',
            'paterno_familiar' => 'FAMILIAR',
            'materno_familiar' => 'PRUEBA',
            'nombres_familiar' => 'UNO',
            'nacimiento_familiar' => '1990-01-01',
            'tipo_familiar' => 'HERMANO',
            'source_row_number' => 1,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function sbsSummaryRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'cod_sbs' => '001',
            'fecha_reporte_sbs' => '202607',
            'cantidad_empresas' => 2,
            'calificacion_normal' => '100.0000',
            'calificacion_cpp' => '0.0000',
            'calificacion_deficiente' => '0.0000',
            'calificacion_dudoso' => '0.0000',
            'calificacion_perdida' => '0.0000',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function sbsDetailRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'fecha_reporte_sbs' => '202607',
            'cod_sbs' => '001',
            'entidad' => 'BANCO DE PRUEBA',
            'tipo_credito' => 'CONSUMO',
            'condicion' => 'NOR',
            'monto' => '5000.0000',
            'dias_atraso' => 0,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function employmentRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'periodo' => '202607',
            'ruc' => '20100000001',
            'nombre_empresa' => 'EMPRESA DE PRUEBA S.A.C.',
            'sueldo' => '1000.0000',
            'situacion' => 'ACTIVO',
        ], $overrides);
    }
}
