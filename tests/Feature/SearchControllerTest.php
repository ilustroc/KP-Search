<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SearchControllerTest extends TestCase
{
    public function test_home_renders_search_without_authentication(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSeeText('Consulta de Clientes')
            ->assertSeeText('Número de documento')
            ->assertSee('name="documento"', false)
            ->assertDontSeeText('Iniciar sesión');
    }

    public function test_responses_prevent_caching_and_indexing(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertHeaderContains('Cache-Control', 'private')
            ->assertHeaderContains('Cache-Control', 'no-store')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive, nosnippet');
    }

    #[DataProvider('invalidDocuments')]
    public function test_search_rejects_invalid_document(string $document): void
    {
        $response = $this->from('/')->post('/buscar', ['documento' => $document]);

        $response
            ->assertRedirect('/')
            ->assertSessionHasErrors('documento');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidDocuments(): array
    {
        return [
            'empty' => [''],
            'seven digits' => ['1234567'],
            'nine digits' => ['123456789'],
            'eleven digits' => ['20123456789'],
            'letters' => ['12AB5678'],
            'internal space' => ['1234 5678'],
            'hyphen' => ['1234-5678'],
            'quote' => ["1234567'"],
            'semicolon' => ['1234567;'],
            'sql comment' => ['1234--56'],
            'html' => ['<b>12345678</b>'],
            'javascript' => ['javascript:1'],
        ];
    }

    public function test_search_trims_document_before_validation(): void
    {
        $this->createGoldPersonIndex();

        $response = $this->post('/buscar', ['documento' => '  12345678  ']);

        $response
            ->assertOk()
            ->assertViewHas('document', '12345678')
            ->assertSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_renders_person_and_calculated_age(): void
    {
        $this->travelTo('2026-10-15 10:00:00');
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_person_index')->insert($this->personRecord());
        DB::connection('mysql_gold')->table('search_telefonos')->insert($this->phoneRecord([
            'documento' => '12345678',
        ]));

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertSeeText('CLIENTE DE PRUEBA')
            ->assertSeeText('Documento 12345678')
            ->assertSeeText('Edad 36')
            ->assertSeeText('903211606')
            ->assertSeeText('Principal')
            ->assertSeeText('Situación financiera')
            ->assertSeeText('No se encontraron datos');
    }

    public function test_search_renders_person_when_phone_dataset_has_no_rows(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_person_index')->insert($this->personRecord());

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertSeeText('CLIENTE DE PRUEBA')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_renders_phones_when_person_does_not_exist(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_telefonos')->insert($this->phoneRecord());

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('903211606')
            ->assertSeeText('ENTEL')
            ->assertSeeText('01/07/2026')
            ->assertSeeText('Datos personales')
            ->assertSeeText('No se encontraron datos')
            ->assertDontSeeText('Nombre no disponible')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_renders_not_found_when_person_and_phones_are_empty(): void
    {
        $this->createGoldPersonIndex();

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_phone_table_renders_operator_and_whatsapp_action(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_telefonos')->insert($this->phoneRecord());

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('OPERADOR')
            ->assertDontSeeText('OPERADOR / ORIGEN')
            ->assertSeeText('1')
            ->assertSee('https://api.whatsapp.com/send/?phone=51903211606&text&type=phone_number&app_absent=0', false)
            ->assertSee('target="_blank"', false)
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_search_displays_query_error_when_gold_connection_fails(): void
    {
        $this->configureGoldConnection();

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertSeeText('No fue posible realizar la consulta en este momento.')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_rejects_eleven_digit_document_with_dni_message(): void
    {
        $response = $this->from('/')->post('/buscar', ['documento' => '20123456789']);

        $response
            ->assertRedirect('/')
            ->assertSessionHasErrors([
                'documento' => 'Ingrese un DNI válido de 8 dígitos.',
            ]);
    }

    public function test_document_is_not_added_to_result_url(): void
    {
        $this->createGoldPersonIndex();

        $response = $this->post('/buscar', ['documento' => '12345678']);
        $searchPath = parse_url(route('search.store'), PHP_URL_PATH);

        $response->assertOk();
        $this->assertSame('/buscar', $searchPath);
        $this->assertStringNotContainsString('12345678', (string) $searchPath);
    }

    public function test_person_data_is_html_escaped(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_person_index')->insert($this->personRecord([
            'nombre_completo' => '<script>alert("xss")</script>',
        ]));

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false);
    }

    public function test_search_is_rate_limited_by_ip(): void
    {
        $this->createGoldPersonIndex();

        for ($attempt = 1; $attempt <= 30; $attempt++) {
            $this->post('/buscar', ['documento' => '12345678'])->assertOk();
        }

        $this->post('/buscar', ['documento' => '12345678'])->assertTooManyRequests();
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function personRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '12345678',
            'tipo_documento' => 'DNI',
            'nombre_completo' => 'CLIENTE DE PRUEBA',
            'paterno' => 'PRUEBA',
            'materno' => 'INTERNA',
            'nombres' => 'CLIENTE',
            'fecha_nacimiento' => '1990-10-14',
            'sexo' => 'F',
            'estado_civil' => 'SOLTERO',
            'direccion_origen' => 'LIMA',
            'source_code' => 'TEST',
            'batch_id' => 1,
            'updated_at' => '2026-10-15 09:30:00',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function phoneRecord(array $overrides = []): array
    {
        return array_merge([
            'documento' => '01115105',
            'telefono' => '903211606',
            'operador' => 'ENTEL',
            'fecha_data' => '2026-07-01',
        ], $overrides);
    }
}
