<?php
namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToEmpresa;

class Cotizacion extends Model
{
    use HasFactory, SoftDeletes, PertenecerEmpresa, BelongsToEmpresa;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'empresa_id',
        'numero', 'consecutivo',
        'cliente_id', 'cliente_nombre', 'cliente_documento',
        'cliente_email', 'cliente_telefono', 'cliente_direccion',
        'fecha_emision', 'fecha_vencimiento',
        'subtotal', 'descuento', 'iva', 'total',
        'estado', 'forma_pago', 'plazo_pago',
        'observaciones', 'terminos', 'factura_id', 'user_id',
    ];

    protected $casts = [
        'fecha_emision'     => 'date',
        'fecha_vencimiento' => 'date',
    ];

    public function cliente()  { return $this->belongsTo(Cliente::class); }
    public function items()    { return $this->hasMany(CotizacionItem::class)->orderBy('orden'); }
    public function factura()  { return $this->belongsTo(Factura::class); }
    public function usuario()  { return $this->belongsTo(User::class, 'user_id'); }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'enviada'    => 'blue',
            'aceptada'   => 'emerald',
            'rechazada'  => 'red',
            'vencida'    => 'orange',
            'convertida' => 'purple',
            default      => 'amber',
        };
    }

    public function getVencidaAttribute(): bool
    {
        return $this->fecha_vencimiento < now() &&
               !in_array($this->estado, ['aceptada', 'rechazada', 'convertida']);
    }

    public static function siguienteConsecutivo(): array
    {
        $ultimo      = static::withTrashed()->max('consecutivo') ?? 0;
        $consecutivo = $ultimo + 1;
        $numero      = 'COT-' . date('Y') . '-' . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);
        return compact('consecutivo', 'numero');
    }
}
