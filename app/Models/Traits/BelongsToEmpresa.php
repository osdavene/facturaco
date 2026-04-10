<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToEmpresa
{
    public static function bootBelongsToEmpresa(): void
    {
        // ── Global scope: filtrar siempre por empresa activa ──
        static::addGlobalScope('empresa', function (Builder $builder) {
            $empresaId = session('empresa_activa_id');
            if ($empresaId) {
                $builder->where(
                    (new static)->getTable() . '.empresa_id',
                    $empresaId
                );
            }
        });

        // ── Al crear: asignar empresa_id automáticamente ──────
        static::creating(function ($model) {
            if (empty($model->empresa_id)) {
                $model->empresa_id = session('empresa_activa_id');
            }
        });
    }

    public function empresa()
    {
        return $this->belongsTo(\App\Models\Empresa::class);
    }
}