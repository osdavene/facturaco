<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $table = 'planes';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'precio',
        'duracion_meses',
        'limite_facturas_mes',
        'limite_usuarios',
        'limite_productos',
        'soporta_dian',
        'soporta_pos',
        'soporta_nomina',
        'soporta_contabilidad',
        'color',
        'destacado',
        'activo',
        'orden',
    ];

    protected $casts = [
        'precio'               => 'decimal:2',
        'duracion_meses'       => 'integer',
        'limite_facturas_mes'  => 'integer',
        'limite_usuarios'      => 'integer',
        'limite_productos'     => 'integer',
        'soporta_dian'         => 'boolean',
        'soporta_pos'          => 'boolean',
        'soporta_nomina'       => 'boolean',
        'soporta_contabilidad' => 'boolean',
        'destacado'            => 'boolean',
        'activo'               => 'boolean',
        'orden'                => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->nombre);
            }
        });
    }

    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'plan_id');
    }

    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio, 0, ',', '.');
    }

    public function getLimiteFacturasTextoAttribute(): string
    {
        return $this->limite_facturas_mes
            ? number_format($this->limite_facturas_mes) . ' facturas / mes'
            : 'Facturas ilimitadas';
    }
}
