<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait que aísla automáticamente los datos por empresa.
 *
 * - Global Scope: todas las queries se filtran por session('empresa_activa_id')
 * - creating hook: asigna empresa_id automáticamente al crear un registro
 *
 * Úsalo en todos los modelos "tenant" (clientes, facturas, productos, etc.)
 */
trait PertenecerEmpresa
{
    protected static function bootPertenecerEmpresa(): void
    {
        // ── Auto-asignar empresa_id al crear ──────────────────────
        static::creating(function (Model $model) {
            if (empty($model->empresa_id)) {
                $model->empresa_id = session('empresa_activa_id');
            }
        });

        // ── Global scope: filtrar por empresa activa ──────────────
        static::addGlobalScope('empresa', function (Builder $query) {
            if ($id = session('empresa_activa_id')) {
                $query->where(
                    $query->getModel()->getTable() . '.empresa_id',
                    $id
                );
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────

    /** Relación con la empresa a la que pertenece este registro */
    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    /** Scope para omitir el filtro de empresa (útil en comandos/webhooks) */
    public function scopeSinFiltroEmpresa(Builder $query): Builder
    {
        return $query->withoutGlobalScope('empresa');
    }
}
