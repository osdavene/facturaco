<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    protected $table = 'empresa';

    protected $fillable = [
        'razon_social', 'nombre_comercial', 'nit', 'digito_verificacion',
        'tipo_persona', 'regimen',
        'email', 'telefono', 'celular', 'sitio_web',
        'pais', 'departamento', 'municipio', 'direccion',
        'prefijo_factura', 'resolucion_numero', 'resolucion_fecha',
        'resolucion_vencimiento', 'consecutivo_desde', 'consecutivo_hasta',
        'consecutivo_actual', 'clave_tecnica', 'factura_electronica',
        'logo', 'moneda', 'decimales', 'pie_factura', 'terminos_condiciones',
        'iva_defecto', 'retefuente_defecto', 'reteica_defecto',
    ];

    protected $casts = [
        'resolucion_fecha'       => 'date',
        'resolucion_vencimiento' => 'date',
        'factura_electronica'    => 'boolean',
    ];

    public function getNitFormateadoAttribute(): string
    {
        if ($this->digito_verificacion) {
            return $this->nit . '-' . $this->digito_verificacion;
        }
        return $this->nit;
    }

    public function getResolucionVigenteAttribute(): bool
    {
        if (!$this->resolucion_vencimiento) return false;
        return $this->resolucion_vencimiento >= now()->startOfDay();
    }

    public function getDiasParaVencerAttribute(): int
    {
        if (!$this->resolucion_vencimiento) return 0;
        return max(0, now()->startOfDay()->diffInDays($this->resolucion_vencimiento, false));
    }

    // Obtener o crear la empresa (singleton)
    public static function obtener(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'razon_social'   => 'MI EMPRESA S.A.S',
                'nit'            => '900000000',
                'prefijo_factura'=> 'FE',
                'moneda'         => 'COP',
                'iva_defecto'    => 19,
            ]
        );
    }
}