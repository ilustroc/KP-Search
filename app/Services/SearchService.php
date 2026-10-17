<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use Throwable;

class SearchService
{
    /** @var list<string> */
    private const PERSON_COLUMNS = [
        'documento',
        'tipo_documento',
        'nombre_completo',
        'paterno',
        'materno',
        'nombres',
        'fecha_nacimiento',
        'sexo',
        'estado_civil',
        'direccion_origen',
        'source_code',
        'batch_id',
        'updated_at',
    ];

    /** @var list<string> */
    private const PHONE_COLUMNS = [
        'documento',
        'telefono',
        'operador',
        'fecha_data',
    ];

    /** @var list<string> */
    private const EMAIL_COLUMNS = [
        'correo',
    ];

    /** @var list<string> */
    private const ADDRESS_COLUMNS = [
        'direccion',
        'departamento',
        'provincia',
        'distrito',
        'data_procedencia',
    ];

    /** @var list<string> */
    private const VEHICLE_COLUMNS = [
        'placa',
        'marca',
        'clase',
        'anio_compra',
        'anio_fabricacion',
        'numero_transferencias',
    ];

    /** @var list<string> */
    private const PROPERTY_COLUMNS = [
        'partida_registral',
        'zona_registral',
        'oficina',
    ];

    /** @var list<string> */
    private const SBS_SUMMARY_COLUMNS = [
        'cod_sbs',
        'fecha_reporte_sbs',
        'cantidad_empresas',
        'calificacion_normal',
        'calificacion_cpp',
        'calificacion_deficiente',
        'calificacion_dudoso',
        'calificacion_perdida',
    ];

    /** @var list<string> */
    private const SBS_DETAIL_COLUMNS = [
        'fecha_reporte_sbs',
        'cod_sbs',
        'entidad',
        'tipo_credito',
        'condicion',
        'monto',
        'dias_atraso',
    ];

    /** @var list<string> */
    private const EMPLOYMENT_COLUMNS = [
        'periodo',
        'ruc',
        'nombre_empresa',
        'sueldo',
        'situacion',
    ];

    /**
     * @return array{
     *     person: array<string, mixed>|null,
     *     gold_query_failed: bool,
     *     emails: list<array<string, mixed>>,
     *     phones: list<array<string, mixed>>,
     *     addresses: list<array<string, mixed>>,
     *     vehicles: list<array<string, mixed>>,
     *     properties: list<array<string, mixed>>,
     *     family: list<array<string, mixed>>,
     *     sbs_summary: list<array<string, mixed>>,
     *     sbs_accounts: list<array<string, mixed>>,
     *     essalud: list<array<string, mixed>>
     * }
     */
    public function search(string $document): array
    {
        $dataset = 'PERSONAS';

        try {
            $person = $this->findPerson($document);

            $dataset = 'TELEFONOS';
            $phones = $this->findPhones($document);

            $dataset = 'CORREOS';
            $emails = $this->findEmails($document);

            $dataset = 'DIRECCIONES';
            $addresses = $this->findAddresses($document);

            $dataset = 'VEHICULOS';
            $vehicles = $this->findVehicles($document);

            $dataset = 'PROPIEDADES';
            $properties = $this->findProperties($document);

            $dataset = 'SBS_RESUMEN';
            $sbsSummary = $this->findSbsSummary($document);

            $dataset = 'SBS_DETALLE';
            $sbsAccounts = $this->findSbsDetails($document);

            $dataset = 'LABORAL_ESSALUD';
            $essalud = $this->findEmployment($document);
        } catch (Throwable $exception) {
            $this->logGoldQueryFailure($dataset, $exception);

            return $this->result(goldQueryFailed: true);
        }

        return $this->result(
            person: $person,
            phones: $phones,
            emails: $emails,
            addresses: $addresses,
            vehicles: $vehicles,
            properties: $properties,
            sbsSummary: $sbsSummary,
            sbsAccounts: $sbsAccounts,
            essalud: $essalud,
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function findPerson(string $document): ?array
    {
        $person = DB::connection((string) config('search.connection'))
            ->table((string) config('search.personas.object'))
            ->select(self::PERSON_COLUMNS)
            ->where('documento', $document)
            ->limit(1)
            ->first();

        if (! $person instanceof stdClass) {
            return null;
        }

        $personData = (array) $person;
        $personData['edad'] = $this->calculateAge($personData['fecha_nacimiento'] ?? null);
        $personData['fecha_nacimiento_formateada'] = $this->formatDate($personData['fecha_nacimiento'] ?? null);
        $personData['updated_at_formateado'] = $this->formatDateTime($personData['updated_at'] ?? null);

        return $personData;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findPhones(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.telefonos.object'))
            ->select(self::PHONE_COLUMNS)
            ->where('documento', $document)
            ->orderByDesc('fecha_data')
            ->orderBy('telefono')
            ->get()
            ->map(function (stdClass $phone): array {
                $phoneData = (array) $phone;
                $phoneData['fecha_data_formateada'] = $this->formatDate($phoneData['fecha_data'] ?? null);

                return $phoneData;
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findEmails(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.correos.object'))
            ->select(self::EMAIL_COLUMNS)
            ->where('documento', $document)
            ->orderBy('correo')
            ->get()
            ->map(static fn (stdClass $email): array => (array) $email)
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findAddresses(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.direcciones.object'))
            ->select(self::ADDRESS_COLUMNS)
            ->where('documento', $document)
            ->orderByRaw('procedencia_anio is null')
            ->orderByDesc('procedencia_anio')
            ->orderByRaw('data_procedencia is null')
            ->orderByDesc('data_procedencia')
            ->orderBy('direccion')
            ->get()
            ->map(static fn (stdClass $address): array => (array) $address)
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findVehicles(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.vehiculos.object'))
            ->select(self::VEHICLE_COLUMNS)
            ->where('documento', $document)
            ->orderByRaw('anio_compra is null')
            ->orderByDesc('anio_compra')
            ->orderByRaw('anio_fabricacion is null')
            ->orderByDesc('anio_fabricacion')
            ->orderByRaw('placa is null')
            ->orderBy('placa')
            ->get()
            ->map(static fn (stdClass $vehicle): array => (array) $vehicle)
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findProperties(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.propiedades.object'))
            ->select(self::PROPERTY_COLUMNS)
            ->where('documento', $document)
            ->orderBy('oficina')
            ->orderBy('partida_registral')
            ->get()
            ->map(static fn (stdClass $property): array => (array) $property)
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findSbsSummary(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.sbs_resumen.object'))
            ->select(self::SBS_SUMMARY_COLUMNS)
            ->where('documento', $document)
            ->orderByDesc('fecha_reporte_sbs')
            ->orderBy('cod_sbs')
            ->get()
            ->map(function (stdClass $summary): array {
                $summaryData = (array) $summary;
                $summaryData['fecha_reporte_formateada'] = $this->formatPeriod($summaryData['fecha_reporte_sbs'] ?? null);

                foreach (['normal', 'cpp', 'deficiente', 'dudoso', 'perdida'] as $rating) {
                    $summaryData["calificacion_{$rating}_formateada"] = $this->formatPercentage(
                        $summaryData["calificacion_{$rating}"] ?? null,
                    );
                }

                return $summaryData;
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findSbsDetails(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.sbs_detalle.object'))
            ->select(self::SBS_DETAIL_COLUMNS)
            ->where('documento', $document)
            ->orderByDesc('fecha_reporte_sbs')
            ->orderBy('entidad')
            ->orderBy('tipo_credito')
            ->get()
            ->map(function (stdClass $detail): array {
                $detailData = (array) $detail;
                $detailData['fecha_reporte_formateada'] = $this->formatPeriod($detailData['fecha_reporte_sbs'] ?? null);
                $detailData['condicion_formateada'] = $this->formatSbsCondition($detailData['condicion'] ?? null);
                $detailData['monto_formateado'] = $this->formatNumber($detailData['monto'] ?? null);
                $detailData['dias_atraso_formateados'] = $this->formatDaysLate($detailData['dias_atraso'] ?? null);

                return $detailData;
            })
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function findEmployment(string $document): array
    {
        return DB::connection((string) config('search.connection'))
            ->table((string) config('search.laboral_essalud.object'))
            ->select(self::EMPLOYMENT_COLUMNS)
            ->where('documento', $document)
            ->orderByDesc('periodo')
            ->orderBy('nombre_empresa')
            ->orderBy('ruc')
            ->get()
            ->map(function (stdClass $employment): array {
                $employmentData = (array) $employment;
                $employmentData['periodo_formateado'] = $this->formatPeriod($employmentData['periodo'] ?? null);
                $employmentData['empresa_formateada'] = $this->valueOrDefault(
                    $employmentData['nombre_empresa'] ?? null,
                    'Empresa no informada',
                );
                $employmentData['sueldo_formateado'] = $this->formatNumber($employmentData['sueldo'] ?? null);

                return $employmentData;
            })
            ->all();
    }

    /**
     * @return array{
     *     person: array<string, mixed>|null,
     *     gold_query_failed: bool,
     *     emails: list<array<string, mixed>>,
     *     phones: list<array<string, mixed>>,
     *     addresses: list<array<string, mixed>>,
     *     vehicles: list<array<string, mixed>>,
     *     properties: list<array<string, mixed>>,
     *     family: list<array<string, mixed>>,
     *     sbs_summary: list<array<string, mixed>>,
     *     sbs_accounts: list<array<string, mixed>>,
     *     essalud: list<array<string, mixed>>
     * }
     */
    private function result(
        ?array $person = null,
        array $phones = [],
        array $emails = [],
        array $addresses = [],
        array $vehicles = [],
        array $properties = [],
        array $sbsSummary = [],
        array $sbsAccounts = [],
        array $essalud = [],
        bool $goldQueryFailed = false,
    ): array {
        return [
            'person' => $person,
            'gold_query_failed' => $goldQueryFailed,
            'emails' => $emails,
            'phones' => $phones,
            'addresses' => $addresses,
            'vehicles' => $vehicles,
            'properties' => $properties,
            'family' => [],
            'sbs_summary' => $sbsSummary,
            'sbs_accounts' => $sbsAccounts,
            'essalud' => $essalud,
        ];
    }

    private function logGoldQueryFailure(string $dataset, Throwable $exception): void
    {
        Log::warning('No fue posible consultar el contrato Gold.', [
            'dataset' => $dataset,
            'exception_class' => $exception::class,
        ]);
    }

    private function calculateAge(mixed $birthDate): ?int
    {
        if (! is_string($birthDate) || $birthDate === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($birthDate)->age;
        } catch (Throwable) {
            return null;
        }
    }

    private function formatDate(mixed $date): ?string
    {
        if (! is_string($date) || $date === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($date)->format('d/m/Y');
        } catch (Throwable) {
            return null;
        }
    }

    private function formatDateTime(mixed $date): ?string
    {
        if (! is_string($date) || $date === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($date)->format('d/m/Y H:i');
        } catch (Throwable) {
            return null;
        }
    }

    private function formatPeriod(mixed $period): ?string
    {
        $value = (string) $period;

        if (preg_match('/\A(\d{4})(0[1-9]|1[0-2])\z/', $value, $matches) !== 1) {
            return null;
        }

        return "{$matches[2]}/{$matches[1]}";
    }

    private function formatPercentage(mixed $percentage): ?string
    {
        $formatted = $this->formatNumber($percentage);

        return $formatted === null ? null : "{$formatted}%";
    }

    private function formatNumber(mixed $number): ?string
    {
        if (! is_numeric($number)) {
            return null;
        }

        $formatted = number_format((float) $number, 4, '.', ',');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted === '-0' ? '0' : $formatted;
    }

    private function formatSbsCondition(mixed $condition): ?string
    {
        return match ($condition) {
            'NOR' => 'Normal',
            'CPP' => 'Con problemas potenciales',
            'DEF' => 'Deficiente',
            'DUD' => 'Dudoso',
            'PER' => 'Pérdida',
            null, '' => null,
            default => (string) $condition,
        };
    }

    private function formatDaysLate(mixed $daysLate): ?string
    {
        if (! is_numeric($daysLate)) {
            return null;
        }

        $days = (int) $daysLate;

        return $days === 1 ? '1 día' : "{$days} días";
    }

    private function valueOrDefault(mixed $value, string $default): string
    {
        return is_string($value) && trim($value) !== '' ? $value : $default;
    }
}
