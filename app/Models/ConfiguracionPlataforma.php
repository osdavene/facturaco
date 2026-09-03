<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfiguracionPlataforma extends Model
{
    protected $table = 'configuracion_plataforma';

    protected $fillable = [
        'clave',
        'valor',
        'grupo',
        'descripcion',
    ];

    public static function get(string $clave, mixed $default = null): mixed
    {
        return Cache::remember("config_plat_{$clave}", 3600, function () use ($clave, $default) {
            $registro = static::where('clave', $clave)->first();
            return $registro?->valor ?? $default;
        });
    }

    public static function set(string $clave, mixed $valor, string $grupo = 'general', ?string $descripcion = null): void
    {
        static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor'       => $valor,
                'grupo'       => $grupo,
                'descripcion' => $descripcion,
            ]
        );

        Cache::forget("config_plat_{$clave}");
    }

    public static function grupo(string $grupo): array
    {
        return static::where('grupo', $grupo)->pluck('valor', 'clave')->toArray();
    }
}
