<?php

return [
    'ambiente'             => env('DIAN_AMBIENTE', 'habilitacion'), // habilitacion | produccion
    'certificado_path'     => env('DIAN_CERTIFICADO_PATH'),
    'certificado_password' => env('DIAN_CERTIFICADO_PASSWORD'),

    // Registro de Software ante la DIAN (obligatorio para envío directo)
    'software_id'  => env('DIAN_SOFTWARE_ID'),
    'software_pin' => env('DIAN_SOFTWARE_PIN'),

    // Proveedor tecnológico alternativo (futuro)
    'proveedor'        => env('DIAN_PROVEEDOR', 'directo'),
    'proveedor_api_key' => env('DIAN_PROVEEDOR_API_KEY'),

    'url_habilitacion' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
    'url_produccion'   => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
];
