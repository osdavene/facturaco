<?php

return [
    'ambiente'             => env('DIAN_AMBIENTE', 'habilitacion'), // habilitacion | produccion
    'certificado_path'     => env('DIAN_CERTIFICADO_PATH'),
    'certificado_password' => env('DIAN_CERTIFICADO_PASSWORD'),
    'proveedor'            => env('DIAN_PROVEEDOR', 'directo'),    // directo | nomina | etc
    'proveedor_api_key'    => env('DIAN_PROVEEDOR_API_KEY'),

    'url_habilitacion' => 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc',
    'url_produccion'   => 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc',
];
