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
        try {
            $person = $this->findPerson($document);
        } catch (Throwable $exception) {
            $this->logGoldQueryFailure('PERSONAS', $exception);

            return $this->result(goldQueryFailed: true);
        }

        try {
            $phones = $this->findPhones($document);
        } catch (Throwable $exception) {
            $this->logGoldQueryFailure('TELEFONOS', $exception);

            return $this->result(goldQueryFailed: true);
        }

        return $this->result(person: $person, phones: $phones);
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
    private function result(?array $person = null, array $phones = [], bool $goldQueryFailed = false): array
    {
        return [
            'person' => $person,
            'gold_query_failed' => $goldQueryFailed,
            'emails' => [],
            'phones' => $phones,
            'addresses' => [],
            'vehicles' => [],
            'properties' => [],
            'family' => [],
            'sbs_summary' => [],
            'sbs_accounts' => [],
            'essalud' => [],
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
}
