<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;

/**
 * Extiende el token de Sanctum para incluir empresa_id.
 * Registrado en AppServiceProvider vía Sanctum::usePersonalAccessTokenModel().
 */
class ApiToken extends PersonalAccessToken
{
    protected $fillable = [
        'tokenable_type', 'tokenable_id',
        'empresa_id',
        'name', 'token', 'abilities',
        'last_used_at', 'expires_at',
    ];

    protected $casts = [
        'abilities'    => 'json',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];
}
