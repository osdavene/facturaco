<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\PertenecerGrupo;

class AsientoContable extends Model
{
    use PertenecerGrupo;

    protected $table = 'asientos_contables';

    protected $fillable = [
        'empresa_id', 'numero', 'fecha', 'descripcion', 'tipo',
        'referencia_tipo', 'referencia_id', 'estado',
        'total_debito', 'total_credito', 'created_by',
    ];

    protected $casts = [
        'fecha'         => 'date',
        'total_debito'  => 'decimal:2',
        'total_credito' => 'decimal:2',
    ];

    const TIPOS = [
        'factura' => 'Factura de venta',
        'recibo'  => 'Recibo de caja',
        'compra'  => 'Orden de compra',
        'nomina'  => 'Nómina',
        'manual'  => 'Asiento manual',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function lineas(): HasMany
    {
        return $this->hasMany(AsientoLinea::class, 'asiento_id')->with('cuenta');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }

    public function getEstaBalanceadoAttribute(): bool
    {
        return abs($this->total_debito - $this->total_credito) < 0.01;
    }

    public function getEstadoColorAttribute(): string
    {
        return match($this->estado) {
            'confirmado' => 'emerald',
            'anulado'    => 'red',
            default      => 'slate',
        };
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeConfirmados($query)
    {
        return $query->where('estado', 'confirmado');
    }
}
