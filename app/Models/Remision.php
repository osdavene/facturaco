<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Remision extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'remisiones';

    protected $fillable = [
        'numero', 'consecutivo',
        'cliente_id', 'cliente_nombre', 'cliente_documento',
        'cliente_email', 'cliente_direccion', 'cliente_telefono',
        'fecha_emision', 'fecha_entrega',
        'lugar_entrega', 'transportador', 'guia',
        'subtotal', 'total', 'estado',
        'factura_id', 'observaciones', 'user_id',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_entrega' => 'date',
    ];

    public function cliente()   { return $this->belongsTo(Cliente::class); }
    public function items()     { return $this->hasMany(RemisionItem::class)->orderBy('orden'); }
    public function factura()   { return $this->belongsTo(Factura::class); }
    public function usuario()   { return $this->belongsTo(User::class, 'user_id'); }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'enviada'   => 'blue',
            'entregada' => 'emerald',
            'facturada' => 'purple',
            'anulada'   => 'slate',
            default     => 'amber',
        };
    }

    public static function siguienteConsecutivo(): array
    {
        $ultimo      = static::withTrashed()->max('consecutivo') ?? 0;
        $consecutivo = $ultimo + 1;
        $numero      = 'REM-' . date('Y') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
        return compact('consecutivo', 'numero');
    }
}