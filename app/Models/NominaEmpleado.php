<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NominaEmpleado extends Model
{
    protected $table = 'nomina_empleado';

    protected $fillable = [
        'nomina_id', 'empleado_id',
        // Tiempo
        'dias_trabajados', 'dias_vacaciones', 'dias_incapacidad', 'dias_licencia_remunerada',
        'horas_extras_diurnas', 'horas_extras_nocturnas',
        'horas_extras_fest_diurnas', 'horas_extras_fest_nocturnas',
        'horas_recargo_nocturno',
        // Devengados
        'salario_basico', 'auxilio_transporte', 'valor_horas_extras',
        'comisiones', 'bonificaciones', 'otros_devengados', 'total_devengado',
        // Deducciones empleado
        'ibc', 'deduccion_salud', 'deduccion_pension', 'fondo_solidaridad',
        'retencion_fuente', 'otras_deducciones', 'total_deducciones',
        // Neto
        'neto_pagar',
        // Aportes empleador
        'aporte_salud_empleador', 'aporte_pension_empleador', 'aporte_arl',
        'aporte_caja_compensacion', 'aporte_sena', 'aporte_icbf', 'total_aportes_empleador',
        // Prestaciones acumuladas
        'acumulado_cesantias', 'acumulado_intereses_cesantias',
        'acumulado_prima', 'acumulado_vacaciones',
        'observaciones',
    ];

    protected $casts = [
        'dias_trabajados'             => 'decimal:2',
        'dias_vacaciones'             => 'decimal:2',
        'dias_incapacidad'            => 'decimal:2',
        'dias_licencia_remunerada'    => 'decimal:2',
        'horas_extras_diurnas'        => 'decimal:2',
        'horas_extras_nocturnas'      => 'decimal:2',
        'horas_extras_fest_diurnas'   => 'decimal:2',
        'horas_extras_fest_nocturnas' => 'decimal:2',
        'horas_recargo_nocturno'      => 'decimal:2',
        'salario_basico'              => 'decimal:2',
        'auxilio_transporte'          => 'decimal:2',
        'valor_horas_extras'          => 'decimal:2',
        'comisiones'                  => 'decimal:2',
        'bonificaciones'              => 'decimal:2',
        'otros_devengados'            => 'decimal:2',
        'total_devengado'             => 'decimal:2',
        'ibc'                         => 'decimal:2',
        'deduccion_salud'             => 'decimal:2',
        'deduccion_pension'           => 'decimal:2',
        'fondo_solidaridad'           => 'decimal:2',
        'retencion_fuente'            => 'decimal:2',
        'otras_deducciones'           => 'decimal:2',
        'total_deducciones'           => 'decimal:2',
        'neto_pagar'                  => 'decimal:2',
        'aporte_salud_empleador'      => 'decimal:2',
        'aporte_pension_empleador'    => 'decimal:2',
        'aporte_arl'                  => 'decimal:2',
        'aporte_caja_compensacion'    => 'decimal:2',
        'aporte_sena'                 => 'decimal:2',
        'aporte_icbf'                 => 'decimal:2',
        'total_aportes_empleador'     => 'decimal:2',
        'acumulado_cesantias'         => 'decimal:2',
        'acumulado_intereses_cesantias' => 'decimal:2',
        'acumulado_prima'             => 'decimal:2',
        'acumulado_vacaciones'        => 'decimal:2',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function nomina(): BelongsTo
    {
        return $this->belongsTo(Nomina::class, 'nomina_id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
