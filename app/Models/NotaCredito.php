<?php

namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;

class NotaCredito extends Model
{
    use PertenecerEmpresa;

    protected $table = 'notas_credito';

    protected $fillable = [
        'empresa_id',
        'numero', 'prefijo', 'consecutivo',
        'factura_id', 'factura_numero',
        'cliente_id', 'cliente_nombre', 'cliente_documento',
        'tipo', 'motivo', 'observaciones', 'fecha',
        'subtotal', 'iva', 'total',
        'estado', 'user_id',
    ];

    protected $casts = [
        'fecha'    => 'date',
        'subtotal' => 'decimal:2',
        'iva'      => 'decimal:2',
        'total'    => 'decimal:2',
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

    public static function siguienteConsecutivo(): array
    {
        $ultimo      = self::max('consecutivo') ?? 0;
        $consecutivo = $ultimo + 1;
        $numero      = 'NC-' . str_pad($consecutivo, 5, '0', STR_PAD_LEFT);

        return compact('consecutivo', 'numero');
    }
}
