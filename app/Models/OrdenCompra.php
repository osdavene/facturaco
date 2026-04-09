<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrdenCompra extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'numero', 'consecutivo',
        'proveedor_id', 'proveedor_nombre', 'proveedor_documento',
        'fecha_emision', 'fecha_esperada', 'fecha_recepcion',
        'subtotal', 'iva', 'descuento', 'total',
        'estado', 'forma_pago', 'plazo_pago',
        'observaciones', 'notas_recepcion', 'user_id',
    ];

    protected $casts = [
        'fecha_emision'   => 'date',
        'fecha_esperada'  => 'date',
        'fecha_recepcion' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function items()
    {
        return $this->hasMany(OrdenCompraItem::class)->orderBy('orden');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'aprobada'  => 'blue',
            'recibida'  => 'emerald',
            'enviada'   => 'cyan',
            'anulada'   => 'slate',
            default     => 'amber',
        };
    }

    public static function siguienteConsecutivo(): array
    {
        $ultimo      = static::withTrashed()->max('consecutivo') ?? 0;
        $consecutivo = $ultimo + 1;
        $numero      = 'OC-' . date('Y') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
        return compact('consecutivo', 'numero');
    }
}