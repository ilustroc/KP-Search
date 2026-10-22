<?php

namespace Tests\Feature;

use App\Services\SearchService;
use Database\Seeders\LocalPreviewGoldSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class LocalPreviewGoldSeederTest extends TestCase
{
    private string $previewDatabasePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previewDatabasePath = tempnam(sys_get_temp_dir(), 'kp-search-preview-');
        $this->app['env'] = 'local';

        config()->set('database.connections.sqlite_preview', [
            'driver' => 'sqlite',
            'database' => $this->previewDatabasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('search.connection', 'sqlite_preview');
        config()->set('search.preview_enabled', true);

        DB::purge('sqlite_preview');
    }

    protected function tearDown(): void
    {
        DB::purge('sqlite_preview');

        if (is_file($this->previewDatabasePath)) {
            unlink($this->previewDatabasePath);
        }

        parent::tearDown();
    }

    public function test_seeds_the_complete_preview_schema_and_returns_complete_fake_data(): void
    {
        app(LocalPreviewGoldSeeder::class)->run();

        foreach ($this->requiredColumns() as $table => $columns) {
            $this->assertTrue(Schema::connection('sqlite_preview')->hasTable($table));
            $this->assertSame([], array_values(array_diff(
                $columns,
                Schema::connection('sqlite_preview')->getColumnListing($table),
            )));
        }

        $result = app(SearchService::class)->search('12345678');

        $this->assertFalse($result['gold_query_failed']);
        $this->assertSame('MARÍA ELENA TORRES SALAZAR', $result['person']['nombre_completo']);
        $this->assertSame('CARLOS TORRES', $result['person']['padre']);
        $this->assertSame('ELENA SALAZAR', $result['person']['madre']);
        $this->assertCount(2, $result['phones']);
        $this->assertCount(2, $result['addresses']);
        $this->assertCount(1, $result['sbs_summary']);
        $this->assertCount(2, $result['sbs_accounts']);
        $this->assertCount(2, $result['essalud']);
    }

    public function test_rejects_preview_seeding_outside_the_local_environment(): void
    {
        $this->app['env'] = 'production';

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('La previsualización solo está disponible en desarrollo local.');

        app(LocalPreviewGoldSeeder::class)->run();
    }

    /**
     * @return array<string, list<string>>
     */
    private function requiredColumns(): array
    {
        return [
            'search_person_index' => ['documento', 'tipo_documento', 'nombre_completo', 'paterno', 'materno', 'nombres', 'fecha_nacimiento', 'sexo', 'estado_civil', 'direccion_origen', 'padre', 'madre', 'source_code', 'batch_id', 'updated_at'],
            'search_telefonos' => ['documento', 'telefono', 'operador', 'fecha_data'],
            'search_correos' => ['documento', 'correo'],
            'search_direcciones' => ['documento', 'direccion', 'departamento', 'provincia', 'distrito', 'data_procedencia', 'procedencia_anio'],
            'search_vehiculos' => ['documento', 'placa', 'marca', 'clase', 'anio_compra', 'anio_fabricacion', 'numero_transferencias'],
            'search_propiedades' => ['documento', 'partida_registral', 'zona_registral', 'oficina'],
            'search_familiares' => ['gold_family_id', 'documento', 'documento_familiar', 'paterno_familiar', 'materno_familiar', 'nombres_familiar', 'nacimiento_familiar', 'tipo_familiar', 'source_row_number'],
            'search_sbs_resumen' => ['documento', 'cod_sbs', 'fecha_reporte_sbs', 'cantidad_empresas', 'calificacion_normal', 'calificacion_cpp', 'calificacion_deficiente', 'calificacion_dudoso', 'calificacion_perdida'],
            'search_sbs_detalle' => ['documento', 'fecha_reporte_sbs', 'cod_sbs', 'entidad', 'tipo_credito', 'condicion', 'monto', 'dias_atraso'],
            'search_laboral_essalud' => ['documento', 'periodo', 'ruc', 'nombre_empresa', 'sueldo', 'situacion'],
        ];
    }
}
