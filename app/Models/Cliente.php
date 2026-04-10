<?php

namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\BelongsToEmpresa;

class Cliente extends Model
{
    use HasFactory, SoftDeletes, PertenecerEmpresa, BelongsToEmpresa;

    protected $table = 'clientes';

    protected $fillable = [
        'empresa_id',
        'tipo_persona', 'tipo_documento', 'numero_documento',
        'digito_verificacion', 'razon_social', 'nombres', 'apellidos',
        'regimen', 'responsable_iva', 'gran_contribuyente',
        'autoretenedor', 'actividad_economica',
        'retefuente_pct', 'reteiva_pct', 'reteica_pct',
        'email', 'telefono', 'celular',
        'departamento', 'municipio', 'direccion', 'pais',
        'plazo_pago', 'cupo_credito', 'lista_precio',
        'activo', 'observaciones', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'responsable_iva'    => 'boolean',
        'gran_contribuyente' => 'boolean',
        'autoretenedor'      => 'boolean',
        'activo'             => 'boolean',
        'cupo_credito'       => 'decimal:2',
    ];

    public function getNombreCompletoAttribute(): string
    {
        if ($this->tipo_persona === 'juridica') {
            return $this->razon_social ?? '';
        }
        return trim("{$this->nombres} {$this->apellidos}");
    }

    public function getDocumentoFormateadoAttribute(): string
    {
        if ($this->tipo_documento === 'NIT' && $this->digito_verificacion) {
            return $this->numero_documento . '-' . $this->digito_verificacion;
        }
        return $this->numero_documento;
    }

    public function scopeBuscar($query, $texto)
    {
        return $query->where(function($q) use ($texto) {
            $q->where('numero_documento', 'like', "%{$texto}%")
              ->orWhere('razon_social',   'like', "%{$texto}%")
              ->orWhere('nombres',        'like', "%{$texto}%")
              ->orWhere('apellidos',      'like', "%{$texto}%")
              ->orWhere('email',          'like', "%{$texto}%");
        });
    }

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
