<?php

namespace App\Services\Gold;

use Illuminate\Support\Facades\DB;
use Throwable;

class GoldContractChecker
{
    /**
     * @return list<array{module: string, object: string, status: string, detail: string}>
     */
    public function check(): array
    {
        $connectionName = (string) config('search.gold_contract.connection', 'mysql_gold');
        $configuredModules = config('search.gold_contract.modules', []);
        $results = [];

        foreach ($configuredModules as $module => $object) {
            if (! is_string($object) || $object === '') {
                $results[] = [
                    'module' => $module,
                    'object' => '—',
                    'status' => 'unconfigured',
                    'detail' => 'KP-Data-Manager todavía no ha publicado este objeto del contrato.',
                ];

                continue;
            }

            if (preg_match('/\A(?:[A-Za-z0-9_]+\.)?[A-Za-z0-9_]+\z/', $object) !== 1) {
                $results[] = [
                    'module' => $module,
                    'object' => $object,
                    'status' => 'invalid',
                    'detail' => 'El identificador configurado no es válido.',
                ];

                continue;
            }

            try {
                [$schema, $table] = $this->splitObjectName($connectionName, $object);
                $databaseObject = DB::connection($connectionName)->selectOne(
                    'SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                    [$schema, $table],
                );

                $results[] = [
                    'module' => $module,
                    'object' => $object,
                    'status' => $databaseObject === null ? 'missing' : 'available',
                    'detail' => $databaseObject === null
                        ? 'El objeto configurado no existe en Gold.'
                        : 'Objeto publicado y accesible.',
                ];
            } catch (Throwable) {
                $results[] = [
                    'module' => $module,
                    'object' => $object,
                    'status' => 'connection-error',
                    'detail' => 'No fue posible inspeccionar la conexión Gold.',
                ];
            }
        }

        return $results;
    }

    /**
     * @return array{string, string}
     */
    private function splitObjectName(string $connectionName, string $object): array
    {
        if (str_contains($object, '.')) {
            /** @var array{string, string} $segments */
            $segments = explode('.', $object, 2);

            return $segments;
        }

        return [DB::connection($connectionName)->getDatabaseName(), $object];
    }
}
