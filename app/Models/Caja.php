<?php

namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    use HasFactory, PertenecerEmpresa;

    protected $table = 'cajas';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'codigo',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function turnos()
    {
        return $this->hasMany(CajaTurno::class, 'caja_id');
    }

    public function turnoActivo()
    {
        return $this->hasOne(CajaTurno::class, 'caja_id')->where('estado', 'abierto')->latest('id');
    }
}
