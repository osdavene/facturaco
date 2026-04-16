<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsientoLinea extends Model
{
    protected $table = 'asiento_lineas';

    protected $fillable = [
        'asiento_id', 'cuenta_id', 'descripcion', 'debito', 'credito',
    ];

    protected $casts = [
        'debito'  => 'decimal:2',
        'credito' => 'decimal:2',
    ];

    public function asiento(): BelongsTo
    {
        return $this->belongsTo(AsientoContable::class, 'asiento_id');
    }

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(PlanCuenta::class, 'cuenta_id');
    }
}
