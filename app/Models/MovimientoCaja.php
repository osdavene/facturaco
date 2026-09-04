<?php

namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoCaja extends Model
{
    use HasFactory, PertenecerEmpresa;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'empresa_id',
        'caja_turno_id',
        'tipo',
        'monto',
        'motivo',
        'user_id',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
    ];

    public function turno()
    {
        return $this->belongsTo(CajaTurno::class, 'caja_turno_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
