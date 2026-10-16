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
    public function test_search_uses_exact_selects_for_person_and_phones(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_person_index')->insert($this->personRecord());
        DB::connection('mysql_gold')->table('search_telefonos')->insert($this->phoneRecord());
        DB::connection('mysql_gold')->flushQueryLog();
        DB::connection('mysql_gold')->enableQueryLog();

        $result = app(SearchService::class)->search('12345678');
        $queries = DB::connection('mysql_gold')->getQueryLog();

        $this->assertSame('CLIENTE EXACTO', $result['person']['nombre_completo']);
        $this->assertCount(2, $queries);
        $this->assertStringContainsString('where "documento" = ?', $queries[0]['query']);
        $this->assertStringNotContainsString('like', mb_strtolower($queries[0]['query']));
        $this->assertSame(['12345678'], $queries[0]['bindings']);

        $this->assertStringContainsString('where "documento" = ?', $queries[1]['query']);
        $this->assertStringNotContainsString('like', mb_strtolower($queries[1]['query']));
        $this->assertSame(['12345678'], $queries[1]['bindings']);
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
}
