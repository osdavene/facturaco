<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToEmpresa;

class Factura extends Model
{
    use HasFactory, SoftDeletes, BelongsToEmpresa;

    protected $table = 'facturas';

    protected $fillable = [
        'empresa_id',
        'numero', 'prefijo', 'consecutivo', 'tipo',
        'cliente_id', 'cliente_nombre', 'cliente_documento',
        'cliente_direccion', 'cliente_email',
        'fecha_emision', 'fecha_vencimiento',
        'subtotal', 'descuento', 'base_iva', 'iva',
        'retefuente', 'reteiva', 'reteica',
        'total', 'total_pagado',
        'estado', 'observaciones', 'forma_pago', 'plazo_pago',
        'cufe', 'enviada_dian', 'fecha_dian', 'user_id',
    ];

    protected $casts = [
        'fecha_emision'     => 'date',
        'fecha_vencimiento' => 'date',
        'enviada_dian'      => 'boolean',
        'fecha_dian'        => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(FacturaItem::class)->orderBy('orden');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'pagada'  => 'emerald',
            'emitida' => 'blue',
            'vencida' => 'red',
            'anulada' => 'slate',
            default   => 'amber',
        };
    }

    public function getSaldoPendienteAttribute(): float
    {
        return max(0, $this->total - $this->total_pagado);
    }

    public static function siguienteConsecutivo(string $prefijo = ''): array
    {
        if (empty($prefijo)) {
            $prefijo = \App\Models\Empresa::obtener()->prefijo_factura ?? 'FE';
        }

        $ultimo = static::where('prefijo', $prefijo)
            ->withTrashed()
            ->max('consecutivo') ?? 0;

        $consecutivo = $ultimo + 1;
        $numero      = $prefijo . '-' . date('Y') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

        return compact('consecutivo', 'numero');
    }

    protected static function booted(): void
    {
        static::retrieved(function ($factura) {
            if ($factura->estado === 'emitida' &&
                $factura->fecha_vencimiento < now()->startOfDay()) {
                $factura->updateQuietly(['estado' => 'vencida']);
            }
        });
    }
}