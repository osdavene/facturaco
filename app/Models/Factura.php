<?php
namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Factura extends Model
{
    use HasFactory, SoftDeletes, PertenecerEmpresa, LogsActivity;

    protected $table = 'facturas';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'total', 'total_pagado', 'fecha_vencimiento', 'observaciones', 'forma_pago'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('factura');
    }

    public function tapActivity(\Spatie\Activitylog\Contracts\Activity $activity, string $eventName): void
    {
        $activity->description = match($eventName) {
            'created' => 'Factura creada',
            'updated' => 'Factura actualizada',
            'deleted' => 'Factura eliminada',
            default   => $eventName,
        };
    }

    protected $fillable = [
        'empresa_id',
        'numero', 'prefijo', 'consecutivo', 'tipo',
        'cliente_id', 'cliente_nombre', 'cliente_documento',
        'cliente_direccion', 'cliente_email',
        'fecha_emision', 'hora_emision', 'fecha_vencimiento',
        'subtotal', 'descuento', 'base_iva', 'iva',
        'retefuente', 'reteiva', 'reteica',
        'total', 'total_pagado',
        'estado', 'observaciones', 'forma_pago', 'plazo_pago',
        'cufe', 'enviada_dian', 'fecha_dian', 'user_id',
        'caja_turno_id', 'token_pago',
    ];

    protected $casts = [
        'fecha_emision'     => 'date',
        'hora_emision'      => 'string',
        'fecha_vencimiento' => 'date',
        'enviada_dian'      => 'boolean',
        'fecha_dian'        => 'datetime',
    ];

    public function cajaTurno()
    {
        return $this->belongsTo(CajaTurno::class, 'caja_turno_id');
    }

    public function getUrlPagoAttribute(): string
    {
        if (empty($this->token_pago)) {
            $this->token_pago = \Illuminate\Support\Str::random(32);
            $this->saveQuietly();
        }

        return route('factura.pago_publico', ['token' => $this->token_pago]);
    }

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

    public static function siguienteConsecutivo(string $prefijo = '', ?int $empresaId = null): array
    {
        $empresa = $empresaId ? \App\Models\Empresa::find($empresaId) : \App\Models\Empresa::obtener();
        if (empty($prefijo)) {
            $prefijo = $empresa?->prefijo_factura ?? 'FE';
        }

        $empresaId = $empresa?->id;

        $ultimo = static::withoutGlobalScopes()
            ->where('prefijo', $prefijo)
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->withTrashed()
            ->max('consecutivo');

        $desde = (int) ($empresa?->consecutivo_desde ?? 1);

        if ($ultimo === null || $ultimo < $desde) {
            $consecutivo = $desde > 0 ? $desde : 1;
        } else {
            $consecutivo = $ultimo + 1;
        }

        $numero = !empty($prefijo) ? $prefijo . '-' . $consecutivo : (string) $consecutivo;

        // Asegurar que no colisione con facturas previas en la empresa (incluso anuladas/eliminadas)
        while (static::withoutGlobalScopes()
            ->withTrashed()
            ->when($empresaId, fn($q) => $q->where('empresa_id', $empresaId))
            ->where(function ($q) use ($numero, $consecutivo, $prefijo) {
                $q->where('numero', $numero)
                  ->orWhere(function ($q2) use ($consecutivo, $prefijo) {
                      $q2->where('consecutivo', $consecutivo)
                         ->where('prefijo', $prefijo);
                  });
            })
            ->exists()) {
            $consecutivo++;
            $numero = !empty($prefijo) ? $prefijo . '-' . $consecutivo : (string) $consecutivo;
        }

        return compact('consecutivo', 'numero');
    }

    protected static function booted(): void
    {
        static::creating(function ($factura) {
            if (empty($factura->token_pago)) {
                $factura->token_pago = \Illuminate\Support\Str::random(32);
            }
        });

        static::retrieved(function ($factura) {
            if ($factura->estado === 'emitida' &&
                $factura->fecha_vencimiento < now()->startOfDay()) {
                $factura->updateQuietly(['estado' => 'vencida']);
            }
        });
    }
}