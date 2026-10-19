<?php

$localPreviewEnabled = env('APP_ENV') === 'local' && env('SEARCH_LOCAL_PREVIEW', false);

return [
    'connection' => $localPreviewEnabled ? 'sqlite_preview' : 'mysql_gold',
    'preview_enabled' => $localPreviewEnabled,
    'personas' => [
        'object' => 'search_person_index',
    ],
    'telefonos' => [
        'object' => 'search_telefonos',
    ],
    'correos' => [
        'object' => 'search_correos',
    ],
    'direcciones' => [
        'object' => 'search_direcciones',
    ],
    'vehiculos' => [
        'object' => 'search_vehiculos',
    ],
    'propiedades' => [
        'object' => 'search_propiedades',
    ],
    'familiares' => [
        'object' => 'search_familiares',
    ],
    'sbs_resumen' => [
        'object' => 'search_sbs_resumen',
    ],
    'sbs_detalle' => [
        'object' => 'search_sbs_detalle',
    ],
    'laboral_essalud' => [
        'object' => 'search_laboral_essalud',
    ],
];
