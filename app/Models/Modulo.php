<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Modulo extends Model
{
    protected $table = 'modulos';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
        'activo',
        'orden',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer',
    ];

    public function empresas(): BelongsToMany
    {
        return $this->belongsToMany(Empresa::class, 'empresa_modulo')
            ->withPivot(['activo'])
            ->withTimestamps();
    }
}
