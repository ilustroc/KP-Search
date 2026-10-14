<?php

return [
    'context_ttl_minutes' => (int) env('SEARCH_CONTEXT_TTL_MINUTES', 15),

    'audit_hmac_key' => env('SEARCH_AUDIT_HMAC_KEY'),

    'gold_contract' => [
        'connection' => 'mysql_gold',
        'modules' => [
            'summary' => env('GOLD_CONTRACT_SUMMARY_OBJECT'),
            'contact' => env('GOLD_CONTRACT_CONTACT_OBJECT'),
            'addresses' => env('GOLD_CONTRACT_ADDRESSES_OBJECT'),
            'family' => env('GOLD_CONTRACT_FAMILY_OBJECT'),
            'labor' => env('GOLD_CONTRACT_LABOR_OBJECT'),
            'vehicles' => env('GOLD_CONTRACT_VEHICLES_OBJECT'),
            'tax' => env('GOLD_CONTRACT_TAX_OBJECT'),
            'financial' => env('GOLD_CONTRACT_FINANCIAL_OBJECT'),
            'properties' => env('GOLD_CONTRACT_PROPERTIES_OBJECT'),
        ],
    ],

    'modules' => [
        'summary' => ['label' => 'Resumen', 'permission' => 'search.use'],
        'contact' => ['label' => 'Contacto', 'permission' => 'search.view_contact'],
        'addresses' => ['label' => 'Direcciones', 'permission' => 'search.view_addresses'],
        'family' => ['label' => 'Familia', 'permission' => 'search.view_family'],
        'labor' => ['label' => 'Laboral', 'permission' => 'search.view_labor'],
        'vehicles' => ['label' => 'Vehículos', 'permission' => 'search.view_vehicles'],
        'tax' => ['label' => 'SUNAT', 'permission' => 'search.view_tax'],
        'financial' => ['label' => 'SBS', 'permission' => 'search.view_financial'],
        'properties' => ['label' => 'Propiedades', 'permission' => 'search.view_properties'],
    ],
];
