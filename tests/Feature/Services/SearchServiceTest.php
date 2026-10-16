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
        $this->assertCount(6, $queries);

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
}
