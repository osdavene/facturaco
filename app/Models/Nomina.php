<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\PertenecerGrupo;

class Nomina extends Model
{
    use PertenecerGrupo;

    protected $table = 'nominas';

    protected $fillable = [
        'empresa_id', 'nombre',
        'periodo_inicio', 'periodo_fin', 'fecha_pago',
        'periodicidad', 'estado',
        'total_devengado', 'total_deducciones', 'total_neto', 'total_aportes_empleador',
        'observaciones', 'created_by',
    ];

    protected $casts = [
        'periodo_inicio'          => 'date',
        'periodo_fin'             => 'date',
        'fecha_pago'              => 'date',
        'total_devengado'         => 'decimal:2',
        'total_deducciones'       => 'decimal:2',
        'total_neto'              => 'decimal:2',
        'total_aportes_empleador' => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function liquidaciones(): HasMany
    {
        return $this->hasMany(NominaEmpleado::class, 'nomina_id')->with('empleado');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'borrador'  => 'slate',
            'procesada' => 'blue',
            'pagada'    => 'emerald',
            'anulada'   => 'red',
            default     => 'slate',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'borrador'  => 'Borrador',
            'procesada' => 'Procesada',
            'pagada'    => 'Pagada',
            'anulada'   => 'Anulada',
            default     => ucfirst($this->estado),
        };
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->whereNotIn('estado', ['anulada']);
    }
}
