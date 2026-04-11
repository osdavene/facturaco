<?php
namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;


class OrdenCompra extends Model
{
    use HasFactory, SoftDeletes, PertenecerEmpresa, LogsActivity;

    protected $table = 'ordenes_compra';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'total', 'fecha_esperada', 'notas_recepcion', 'observaciones'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('orden_compra');
    }

    public function tapActivity(\Spatie\Activitylog\Contracts\Activity $activity, string $eventName): void
    {
        $activity->description = match($eventName) {
            'created' => 'Orden de compra creada',
            'updated' => 'Orden de compra actualizada',
            'deleted' => 'Orden de compra eliminada',
            default   => $eventName,
        };
    }

    protected $fillable = [
        'empresa_id',
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

    public function proveedor() { return $this->belongsTo(Proveedor::class); }
    public function items()     { return $this->hasMany(OrdenCompraItem::class)->orderBy('orden'); }
    public function usuario()   { return $this->belongsTo(User::class, 'user_id'); }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'aprobada' => 'blue',
            'recibida' => 'emerald',
            'enviada'  => 'cyan',
            'anulada'  => 'slate',
            default    => 'amber',
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
