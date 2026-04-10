<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCredito extends Model
{
    protected $table = 'notas_credito';

    protected $fillable = [
        'numero', 'prefijo', 'consecutivo',
        'factura_id', 'factura_numero',
        'cliente_id', 'cliente_nombre', 'cliente_documento',
        'tipo', 'motivo', 'observaciones', 'fecha',
        'subtotal', 'iva', 'total', 'estado', 'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(NotaCreditoItem::class)->orderBy('orden');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getMotivoTextoAttribute(): string
    {
        return match($this->motivo) {
            'devolucion_mercancia' => 'Devolución de mercancía',
            'descuento_posterior'  => 'Descuento posterior',
            'error_facturacion'    => 'Error en facturación',
            'anulacion'            => 'Anulación',
            'otro'                 => 'Otro',
            default                => $this->motivo,
        };
    }

    public static function siguienteConsecutivo(): array
    {
        $ultimo      = static::max('consecutivo') ?? 0;
        $consecutivo = $ultimo + 1;
        $numero      = 'NC-' . date('Y') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
        return compact('consecutivo', 'numero');
    }
}