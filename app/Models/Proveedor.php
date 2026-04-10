<?php

namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proveedor extends Model
{
    use HasFactory, SoftDeletes, PertenecerEmpresa;

    protected $table = 'proveedores';

    protected $fillable = [
        'empresa_id',
        'tipo_documento', 'numero_documento', 'digito_verificacion',
        'razon_social', 'nombre_contacto', 'cargo_contacto',
        'email', 'telefono', 'celular',
        'departamento', 'municipio', 'direccion',
        'regimen', 'gran_contribuyente', 'autoretenedor',
        'retefuente_pct', 'reteiva_pct', 'reteica_pct',
        'plazo_pago', 'cuenta_bancaria', 'banco', 'tipo_cuenta',
        'cupo_credito', 'activo', 'observaciones', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'gran_contribuyente' => 'boolean',
        'autoretenedor'      => 'boolean',
        'activo'             => 'boolean',
        'cupo_credito'       => 'decimal:2',
    ];

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
            $q->where('razon_social',      'like', "%{$texto}%")
              ->orWhere('numero_documento', 'like', "%{$texto}%")
              ->orWhere('nombre_contacto',  'like', "%{$texto}%")
              ->orWhere('email',            'like', "%{$texto}%");
        });
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
