<?php

namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CajaTurno extends Model
{
    use HasFactory, PertenecerEmpresa;

    protected $table = 'caja_turnos';

    protected $fillable = [
        'empresa_id',
        'caja_id',
        'user_id',
        'monto_apertura',
        'fecha_apertura',
        'monto_cierre_esperado',
        'monto_cierre_real',
        'diferencia',
        'total_ventas_efectivo',
        'total_ventas_tarjeta',
        'total_ventas_transferencia',
        'total_ventas_nequi',
        'total_entradas',
        'total_salidas',
        'estado',
        'observaciones',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_apertura'             => 'datetime',
        'fecha_cierre'               => 'datetime',
        'monto_apertura'             => 'decimal:2',
        'monto_cierre_esperado'      => 'decimal:2',
        'monto_cierre_real'          => 'decimal:2',
        'diferencia'                 => 'decimal:2',
        'total_ventas_efectivo'      => 'decimal:2',
        'total_ventas_tarjeta'       => 'decimal:2',
        'total_ventas_transferencia' => 'decimal:2',
        'total_ventas_nequi'         => 'decimal:2',
        'total_entradas'             => 'decimal:2',
        'total_salidas'              => 'decimal:2',
    ];

    public function caja()
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_turno_id');
    }

    public function facturas()
    {
        return $this->hasMany(Factura::class, 'caja_turno_id');
    }

    /**
     * Recalcula los totales acumulados del turno a partir de facturas y movimientos registrados.
     */
    public function recalcularTotales(): void
    {
        $facturas = $this->facturas()->where('estado', '!=', 'anulada')->get();

        $this->total_ventas_efectivo = $facturas->where('forma_pago', 'contado')->sum('total');
        $this->total_ventas_tarjeta = $facturas->where('forma_pago', 'tarjeta')->sum('total');
        $this->total_ventas_transferencia = $facturas->where('forma_pago', 'transferencia')->sum('total');
        $this->total_ventas_nequi = $facturas->where('forma_pago', 'nequi')->sum('total');

        $this->total_entradas = $this->movimientos()->where('tipo', 'entrada')->sum('monto');
        $this->total_salidas  = $this->movimientos()->where('tipo', 'salida')->sum('monto');

        $this->monto_cierre_esperado = (float) $this->monto_apertura
            + (float) $this->total_ventas_efectivo
            + (float) $this->total_entradas
            - (float) $this->total_salidas;

        if ($this->estado === 'cerrado' && $this->monto_cierre_real !== null) {
            $this->diferencia = (float) $this->monto_cierre_real - (float) $this->monto_cierre_esperado;
        }

        $this->save();
    }

    public function getTotalVentasAttribute(): float
    {
        return (float) $this->total_ventas_efectivo
            + (float) $this->total_ventas_tarjeta
            + (float) $this->total_ventas_transferencia
            + (float) $this->total_ventas_nequi;
    }
}
