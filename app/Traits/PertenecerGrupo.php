<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait para modelos de catálogo compartido (clientes, productos, etc.)
 *
 * - Al crear: guarda empresa_id = raíz del grupo (para que todas las filiales compartan el registro)
 * - Global scope: filtra por todos los IDs del grupo (raíz + filiales)
 *
 * Los IDs del grupo se almacenan en session('empresa_grupo_ids') al seleccionar
 * la empresa activa, para no hacer consultas extra en cada request.
 */
trait PertenecerGrupo
{
    protected static function bootPertenecerGrupo(): void
    {
        // ── Al crear: usar el ID de la raíz del grupo ─────────────
        static::creating(function (Model $model) {
            if (empty($model->empresa_id)) {
                $model->empresa_id = session('empresa_raiz_id') ?? session('empresa_activa_id');
            }
        });

        // ── Global scope: filtrar por todo el grupo ───────────────
        static::addGlobalScope('empresa', function (Builder $query) {
            $ids = session('empresa_grupo_ids');

            if (!empty($ids)) {
                $query->whereIn(
                    $query->getModel()->getTable() . '.empresa_id',
                    $ids
                );
            } elseif ($id = session('empresa_activa_id')) {
                // Fallback si aún no se calculó el grupo (e.g. primera carga)
                $query->where(
                    $query->getModel()->getTable() . '.empresa_id',
                    $id
                );
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }

    public function scopeSinFiltroEmpresa(Builder $query): Builder
    {
        return $query->withoutGlobalScope('empresa');
    }
}
