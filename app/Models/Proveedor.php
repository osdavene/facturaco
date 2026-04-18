<?php

namespace App\Models;

use App\Traits\PertenecerGrupo;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Proveedor extends Model
{
    use PertenecerGrupo, HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'proveedores';

    protected $fillable = [
        'empresa_id',
        'tipo_documento',
        'numero_documento',
        'digito_verificacion',
        'razon_social',
        'nombre_contacto',
        'cargo_contacto',
        'email',
        'telefono',
        'celular',
        'departamento',
        'municipio',
        'direccion',
        'regimen',
        'gran_contribuyente',
        'autoretenedor',
        'retefuente_pct',
        'reteiva_pct',
        'reteica_pct',
        'plazo_pago',
        'cuenta_bancaria',
        'banco',
        'tipo_cuenta',
        'cupo_credito',
        'activo',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'plazo_pago'      => 'decimal:2',
        'retefuente_pct'  => 'decimal:4',
        'activo'          => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logName('proveedor')
            ->logOnly(['*'])
            ->logOnlyDirty();
    }

    /**
     * Productos del proveedor (many-to-many)
     */
    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_proveedor')
                    ->withPivot(['precio_compra_sugerido', 'proveedor_principal', 'created_at', 'updated_at'])
                    ->withTimestamps();
    }

    public function ordenesCompra()
    {
        return $this->hasMany(OrdenCompra::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

