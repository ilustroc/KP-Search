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

    public function test_search_renders_multiple_emails_when_no_person_or_phone_exists(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_correos')->insert([
            $this->emailRecord(['documento' => '01115105', 'correo' => 'uno@example.test']),
            $this->emailRecord(['documento' => '01115105', 'correo' => 'dos@example.test']),
        ]);

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('uno@example.test')
            ->assertSeeText('dos@example.test')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_renders_multiple_addresses_when_no_person_or_phone_exists(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_direcciones')->insert([
            $this->addressRecord(['documento' => '01115105', 'direccion' => 'JR. PACHITEA 298', 'data_procedencia' => 'RENIEC 2025', 'procedencia_anio' => 2025]),
            $this->addressRecord(['documento' => '01115105', 'direccion' => 'JR. PACHITEA 298', 'data_procedencia' => 'RENIEC 2022', 'procedencia_anio' => 2022]),
        ]);

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('JR. PACHITEA 298')
            ->assertSeeText('RENIEC 2025')
            ->assertSeeText('RENIEC 2022')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_renders_vehicle_without_plate_when_it_is_the_only_dataset(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_vehiculos')->insert($this->vehicleRecord([
            'documento' => '01115105',
            'placa' => null,
            'marca' => 'HONDA',
            'clase' => 'MOTOCICLETA',
            'anio_compra' => 2021,
            'anio_fabricacion' => null,
            'numero_transferencias' => null,
        ]));

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('Sin placa')
            ->assertSeeText('HONDA')
            ->assertSeeText('MOTOCICLETA')
            ->assertSeeText('2021')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_renders_properties_when_no_person_or_phone_exists(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_propiedades')->insert([
            $this->propertyRecord(['documento' => '01115105', 'partida_registral' => 'P02086269']),
            $this->propertyRecord(['documento' => '01115105', 'partida_registral' => '11184695']),
        ]);

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('P02086269')
            ->assertSeeText('11184695')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_search_renders_family_when_person_is_absent(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_familiares')->insert([
            $this->familyRecord(['gold_family_id' => 10, 'source_row_number' => 10, 'documento' => '01115105', 'documento_familiar' => '9406251AN', 'paterno_familiar' => 'PEREZ', 'materno_familiar' => 'LOPEZ', 'nombres_familiar' => 'ANA', 'nacimiento_familiar' => '1980-01-15', 'tipo_familiar' => 'H']),
            $this->familyRecord(['gold_family_id' => 11, 'source_row_number' => 11, 'documento' => '01115105', 'documento_familiar' => '9406251AN', 'paterno_familiar' => 'PEREZ', 'materno_familiar' => 'LOPEZ', 'nombres_familiar' => 'ANA', 'nacimiento_familiar' => '1980-01-15', 'tipo_familiar' => 'H']),
            $this->familyRecord(['gold_family_id' => 12, 'source_row_number' => 12, 'documento' => '01115105', 'documento_familiar' => '00000000', 'nacimiento_familiar' => '2024-02-31', 'tipo_familiar' => 'C']),
            $this->familyRecord(['gold_family_id' => 13, 'source_row_number' => 13, 'documento' => '01115105', 'documento_familiar' => '0', 'nacimiento_familiar' => 'SIN FECHA', 'tipo_familiar' => 'G']),
            $this->familyRecord(['gold_family_id' => 14, 'source_row_number' => 14, 'documento' => '01115105', 'documento_familiar' => null, 'paterno_familiar' => null, 'materno_familiar' => null, 'nombres_familiar' => null, 'nacimiento_familiar' => null, 'tipo_familiar' => 'N']),
        ]);

        $response = $this->post('/buscar', ['documento' => '01115105']);
        $content = $response->getContent();

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('Documento familiar')
            ->assertSeeText('PEREZ LOPEZ ANA')
            ->assertSeeText('9406251AN')
            ->assertSeeText('00000000')
            ->assertSeeText('15/01/1980')
            ->assertSeeText('2024-02-31')
            ->assertSeeText('SIN FECHA')
            ->assertSeeText('H')
            ->assertSeeText('C')
            ->assertSeeText('G')
            ->assertSeeText('N')
            ->assertDontSeeText('No se encontró información para el documento ingresado.')
            ->assertSee('overflow-x-auto', false);
        $this->assertMatchesRegularExpression('/Familiares<\/h2>.*?>5<\/span>/s', $content);
        $this->assertSame(2, substr_count($content, '9406251AN'));
    }

    public function test_family_data_is_html_escaped_and_technical_fields_are_not_rendered(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_familiares')->insert($this->familyRecord([
            'nombres_familiar' => '<script>alert("xss")</script>',
            'source_code' => 'INTERNO_NO_VISIBLE',
            'record_hash' => 'hash-no-visible',
        ]));

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertSee('&lt;script&gt;', false)
            ->assertDontSee('<script>alert("xss")</script>', false)
            ->assertDontSeeText('INTERNO_NO_VISIBLE')
            ->assertDontSeeText('hash-no-visible');
    }

    public function test_search_renders_sbs_when_person_is_absent(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_sbs_resumen')->insert([
            $this->sbsSummaryRecord(['documento' => '01115105', 'fecha_reporte_sbs' => '202607', 'cantidad_empresas' => 3, 'calificacion_normal' => '92.4500']),
            $this->sbsSummaryRecord(['documento' => '01115105', 'fecha_reporte_sbs' => '202506', 'cantidad_empresas' => 2, 'calificacion_normal' => '100.0000']),
        ]);
        DB::connection('mysql_gold')->table('search_sbs_detalle')->insert([
            $this->sbsDetailRecord(['documento' => '01115105', 'fecha_reporte_sbs' => '202607', 'entidad' => 'BANCO ALFA', 'tipo_credito' => null, 'condicion' => 'NOR', 'monto' => null, 'dias_atraso' => null]),
            $this->sbsDetailRecord(['documento' => '01115105', 'fecha_reporte_sbs' => '202607', 'entidad' => 'BANCO BETA', 'condicion' => 'CPP', 'dias_atraso' => 1]),
            $this->sbsDetailRecord(['documento' => '01115105', 'fecha_reporte_sbs' => '202506', 'entidad' => 'BANCO GAMMA', 'condicion' => 'DEF']),
            $this->sbsDetailRecord(['documento' => '01115105', 'fecha_reporte_sbs' => '202506', 'entidad' => 'BANCO DELTA', 'condicion' => 'DUD']),
            $this->sbsDetailRecord(['documento' => '01115105', 'fecha_reporte_sbs' => '202506', 'entidad' => 'BANCO EPSILON', 'condicion' => 'PER']),
        ]);

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('Último reporte:')
            ->assertSeeText('07/2026')
            ->assertSeeText('92.45%')
            ->assertSeeText('Ver historial de reportes (1)')
            ->assertSeeText('BANCO ALFA')
            ->assertSeeText('Con problemas potenciales')
            ->assertSeeText('Deficiente')
            ->assertSeeText('Dudoso')
            ->assertSeeText('Pérdida')
            ->assertSeeText('1 día')
            ->assertDontSeeText('No se encontró información para el documento ingresado.')
            ->assertSee('overflow-x-auto', false);
    }

    public function test_search_renders_employment_when_person_is_absent(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_laboral_essalud')->insert([
            $this->employmentRecord(['documento' => '01115105', 'periodo' => '202607', 'nombre_empresa' => 'EMPRESA ALFA S.A.C.', 'ruc' => '20100000001', 'sueldo' => '1250.5000', 'situacion' => 'ACTIVO']),
            $this->employmentRecord(['documento' => '01115105', 'periodo' => '202607', 'nombre_empresa' => 'EMPRESA BETA S.A.C.', 'ruc' => '20100000002', 'sueldo' => null, 'situacion' => null]),
            $this->employmentRecord(['documento' => '01115105', 'periodo' => '202506', 'nombre_empresa' => null, 'ruc' => '20100000003']),
        ]);

        $response = $this->post('/buscar', ['documento' => '01115105']);

        $response
            ->assertOk()
            ->assertSeeText('Documento 01115105')
            ->assertSeeText('Historial laboral')
            ->assertSeeText('07/2026')
            ->assertSeeText('06/2025')
            ->assertSeeText('EMPRESA ALFA S.A.C.')
            ->assertSeeText('EMPRESA BETA S.A.C.')
            ->assertSeeText('Empresa no informada')
            ->assertSeeText('1,250.5')
            ->assertDontSeeText('No se encontró información para el documento ingresado.');
    }

    public function test_financial_technical_fields_are_not_rendered(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_sbs_detalle')->insert($this->sbsDetailRecord([
            'source_code' => 'INTERNO_NO_VISIBLE',
            'record_hash' => 'hash-no-visible',
            'batch_id' => 999,
        ]));

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertDontSeeText('INTERNO_NO_VISIBLE')
            ->assertDontSeeText('hash-no-visible')
            ->assertDontSeeText('batch_id');
    }

    public function test_search_renders_all_published_gold_datasets_and_their_badges(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_person_index')->insert($this->personRecord());
        DB::connection('mysql_gold')->table('search_telefonos')->insert($this->phoneRecord([
            'documento' => '12345678',
        ]));
        DB::connection('mysql_gold')->table('search_correos')->insert([
            $this->emailRecord(['correo' => 'uno@example.test']),
            $this->emailRecord(['correo' => 'dos@example.test']),
        ]);
        DB::connection('mysql_gold')->table('search_direcciones')->insert([
            $this->addressRecord(['direccion' => 'AV. UNO 100']),
            $this->addressRecord(['direccion' => 'AV. DOS 200']),
        ]);
        DB::connection('mysql_gold')->table('search_vehiculos')->insert([
            $this->vehicleRecord(['placa' => 'AAA-111']),
            $this->vehicleRecord(['placa' => 'BBB-222']),
        ]);
        DB::connection('mysql_gold')->table('search_propiedades')->insert([
            $this->propertyRecord(['partida_registral' => 'P02086269']),
            $this->propertyRecord(['partida_registral' => '11184695']),
        ]);

        $response = $this->post('/buscar', ['documento' => '12345678']);
        $content = $response->getContent();

        $response
            ->assertOk()
            ->assertSeeText('CLIENTE DE PRUEBA')
            ->assertSeeText('903211606')
            ->assertSeeText('uno@example.test')
            ->assertSeeText('AV. UNO 100')
            ->assertSeeText('AAA-111')
            ->assertSeeText('P02086269');
        $this->assertMatchesRegularExpression('/Teléfonos<\/h2>.*?>1<\/span>/s', $content);
        $this->assertMatchesRegularExpression('/Correos<\/h2>.*?>2<\/span>/s', $content);
        $this->assertMatchesRegularExpression('/Direcciones<\/h2>.*?>2<\/span>/s', $content);
        $this->assertMatchesRegularExpression('/Vehículos<\/h2>.*?>2<\/span>/s', $content);
        $this->assertMatchesRegularExpression('/Propiedades<\/h2>.*?>2<\/span>/s', $content);
    }

    public function test_search_displays_query_error_when_gold_connection_fails(): void
    {
        $this->configureGoldConnection();

        $response = $this->post('/buscar', ['documento' => '12345678']);

        $response
            ->assertOk()
            ->assertSeeText('No fue posible realizar la consulta. Inténtalo nuevamente.')
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

    public function test_email_data_is_html_escaped(): void
    {
        $this->createGoldPersonIndex();
        DB::connection('mysql_gold')->table('search_correos')->insert($this->emailRecord([
            'correo' => '<script>alert("xss")</script>',
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
