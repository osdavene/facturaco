<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Proveedor DIAN Activo
    |--------------------------------------------------------------------------
    | Opciones: 'factus' | 'directo' | 'dataico'
    */
    'proveedor'            => env('DIAN_PROVEEDOR', 'factus'),

    // ── Conexión Directa SOAP (Software Propio con Certificado .p12) ──────────
    'ambiente'             => env('DIAN_AMBIENTE', 'habilitacion'), // habilitacion | produccion
    'certificado_path'     => env('DIAN_CERTIFICADO_PATH'),
    'certificado_password' => env('DIAN_CERTIFICADO_PASSWORD'),
    'software_id'          => env('DIAN_SOFTWARE_ID'),
    'software_pin'         => env('DIAN_SOFTWARE_PIN'),
    'clave_tecnica'        => env('DIAN_CLAVE_TECNICA'),
    'url_habilitacion'     => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
    'url_produccion'       => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',

    // ── Proveedor API Factus (Recomendado SaaS) ──────────────────────────────
    'factus' => [
        'ambiente'           => env('FACTUS_AMBIENTE', 'sandbox'), // sandbox | produccion
        'url_sandbox'        => env('FACTUS_URL_SANDBOX', 'https://api-sandbox.factus.com.co'),
        'url_produccion'     => env('FACTUS_URL_PRODUCCION', 'https://api.factus.com.co'),
        'client_id'          => env('FACTUS_CLIENT_ID'),
        'client_secret'      => env('FACTUS_CLIENT_SECRET'),
        'username'           => env('FACTUS_USERNAME'),
        'password'           => env('FACTUS_PASSWORD'),
        'api_token'          => env('FACTUS_API_TOKEN', env('DIAN_PROVEEDOR_API_KEY')),
        'numbering_range_id' => env('FACTUS_NUMBERING_RANGE_ID', 1),
    ],
];
