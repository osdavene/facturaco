<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PertenecerGrupo;

class Empleado extends Model
{
    use PertenecerGrupo, SoftDeletes;

    protected $table = 'empleados';

    protected $fillable = [
        'empresa_id',
        'nombres', 'apellidos',
        'tipo_documento', 'numero_documento',
        'fecha_nacimiento', 'sexo',
        'email', 'telefono', 'direccion',
        'cargo', 'departamento',
        'fecha_ingreso', 'fecha_retiro',
        'tipo_contrato', 'tipo_salario',
        'salario_base', 'periodicidad_pago',
        'nivel_riesgo_arl',
        'eps', 'afp', 'caja_compensacion',
        'banco', 'tipo_cuenta', 'numero_cuenta',
        'activo', 'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_ingreso'    => 'date',
        'fecha_retiro'     => 'date',
        'salario_base'     => 'decimal:2',
        'activo'           => 'boolean',
    ];

    // ── Relaciones ────────────────────────────────────────────────

    public function liquidaciones(): HasMany
    {
        return $this->hasMany(NominaEmpleado::class, 'empleado_id');
    }

    // ── Accessors ─────────────────────────────────────────────────

    public function getNombreCompletoAttribute(): string
    {
        return $this->nombres . ' ' . $this->apellidos;
    }

    public function getAntiguedadAttribute(): string
    {
        $inicio = $this->fecha_ingreso;
        $fin    = $this->fecha_retiro ?? now();
        $diff   = $inicio->diff($fin);

        $partes = [];
        if ($diff->y > 0) $partes[] = $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) $partes[] = $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
        if (empty($partes)) $partes[] = $diff->d . ' día' . ($diff->d > 1 ? 's' : '');

        return implode(', ', $partes);
    }

    public function getTipoContratoLabelAttribute(): string
    {
        return match($this->tipo_contrato) {
            'indefinido'             => 'Término Indefinido',
            'fijo'                   => 'Término Fijo',
            'obra_labor'             => 'Obra o Labor',
            'prestacion_servicios'   => 'Prestación de Servicios',
            default                  => ucfirst($this->tipo_contrato),
        };
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
