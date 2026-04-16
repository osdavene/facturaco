<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanCuenta extends Model
{
    protected $table = 'plan_cuentas';

    protected $fillable = [
        'empresa_id', 'codigo', 'nombre', 'tipo', 'naturaleza',
        'nivel', 'cuenta_padre_id', 'acepta_movimientos', 'activo',
    ];

    protected $casts = [
        'acepta_movimientos' => 'boolean',
        'activo'             => 'boolean',
        'nivel'              => 'integer',
    ];

    // ── Tipos y etiquetas ─────────────────────────────────────────

    const TIPOS = [
        'activo'     => 'Activo',
        'pasivo'     => 'Pasivo',
        'patrimonio' => 'Patrimonio',
        'ingreso'    => 'Ingreso',
        'gasto'      => 'Gasto',
        'costo'      => 'Costo',
    ];

    // Clases de cuentas del PUC colombiano
    const CLASE_TIPO = [
        '1' => 'activo',
        '2' => 'pasivo',
        '3' => 'patrimonio',
        '4' => 'ingreso',
        '5' => 'gasto',
        '6' => 'costo',
        '7' => 'costo',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function padre(): BelongsTo
    {
        return $this->belongsTo(PlanCuenta::class, 'cuenta_padre_id');
    }

    public function hijas(): HasMany
    {
        return $this->hasMany(PlanCuenta::class, 'cuenta_padre_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(AsientoLinea::class, 'cuenta_id');
    }

    // ── Scopes ────────────────────────────────────────────────────

    /** Cuentas visibles para una empresa: las estándar (NULL) + las propias */
    public function scopeDeEmpresa($query, $empresaId)
    {
        return $query->where(function ($q) use ($empresaId) {
            $q->whereNull('empresa_id')->orWhere('empresa_id', $empresaId);
        });
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    public function scopeConMovimientos($query)
    {
        return $query->where('acepta_movimientos', true);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? ucfirst($this->tipo);
    }

    public function getCodigoNombreAttribute(): string
    {
        return $this->codigo . ' - ' . $this->nombre;
    }

    /** Saldo de la cuenta en un rango de fechas */
    public function saldo(?string $desde = null, ?string $hasta = null): float
    {
        $q = $this->lineas()
            ->join('asientos_contables', 'asiento_lineas.asiento_id', '=', 'asientos_contables.id')
            ->where('asientos_contables.estado', 'confirmado');

        if ($desde) $q->where('asientos_contables.fecha', '>=', $desde);
        if ($hasta) $q->where('asientos_contables.fecha', '<=', $hasta);

        $debito  = (float) $q->sum('asiento_lineas.debito');
        $credito = (float) $q->sum('asiento_lineas.credito');

        // Cuentas de naturaleza débito: saldo = débito - crédito
        // Cuentas de naturaleza crédito: saldo = crédito - débito
        return $this->naturaleza === 'debito'
            ? $debito - $credito
            : $credito - $debito;
    }
}
