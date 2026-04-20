<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DianEvento extends Model
{
    protected $table = 'dian_eventos';

    protected $fillable = [
        'factura_id', 'empresa_id',
        'tipo', 'estado',
        'cufe', 'codigo_respuesta', 'descripcion', 'errores', 'payload',
        'actor_nombre', 'actor_documento', 'nota',
    ];

    protected $casts = [
        'errores' => 'array',
        'payload' => 'array',
    ];

    // ── Tipos ─────────────────────────────────────────────────────────────────

    const TIPO_ENVIO             = 'envio';
    const TIPO_CONSULTA          = 'consulta';
    const TIPO_ACUSE_RECIBO      = 'acuse_recibo';       // evento 032 comprador
    const TIPO_RECIBO_BIEN       = 'recibo_bien';        // evento 033 comprador
    const TIPO_ACEPTACION        = 'aceptacion';         // evento 034 comprador
    const TIPO_RECHAZO_COMPRADOR = 'rechazo_comprador';  // evento 036 comprador
    const TIPO_RECHAZO_DIAN      = 'rechazo_dian';

    const ESTADO_PENDIENTE   = 'pendiente';
    const ESTADO_PROCESANDO  = 'procesando';
    const ESTADO_EXITOSO     = 'exitoso';
    const ESTADO_FALLIDO     = 'fallido';

    // ── Relaciones ────────────────────────────────────────────────────────────

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            self::TIPO_ENVIO             => 'Envío a DIAN',
            self::TIPO_CONSULTA          => 'Consulta estado',
            self::TIPO_ACUSE_RECIBO      => 'Acuse de recibo',
            self::TIPO_RECIBO_BIEN       => 'Recibo de bien/servicio',
            self::TIPO_ACEPTACION        => 'Aceptación expresa',
            self::TIPO_RECHAZO_COMPRADOR => 'Rechazo del comprador',
            self::TIPO_RECHAZO_DIAN      => 'Rechazo DIAN',
            default                      => ucfirst($this->tipo),
        };
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_EXITOSO    => 'emerald',
            self::ESTADO_FALLIDO    => 'red',
            self::ESTADO_PROCESANDO => 'amber',
            default                 => 'slate',
        };
    }

    // ── Helpers estáticos ─────────────────────────────────────────────────────

    public static function registrar(
        Factura $factura,
        string  $tipo,
        string  $estado,
        array   $datos = []
    ): self {
        return static::create([
            'factura_id'      => $factura->id,
            'empresa_id'      => $factura->empresa_id,
            'tipo'            => $tipo,
            'estado'          => $estado,
            'cufe'            => $datos['cufe']             ?? null,
            'codigo_respuesta'=> $datos['codigo']           ?? null,
            'descripcion'     => $datos['descripcion']      ?? null,
            'errores'         => $datos['errores']          ?? null,
            'payload'         => $datos['payload']          ?? null,
            'actor_nombre'    => $datos['actor_nombre']     ?? null,
            'actor_documento' => $datos['actor_documento']  ?? null,
            'nota'            => $datos['nota']             ?? null,
        ]);
    }
}
