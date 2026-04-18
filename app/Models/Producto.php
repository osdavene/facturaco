<?php

namespace App\Models;

use App\Traits\PertenecerGrupo;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Producto extends Model
{
    use PertenecerGrupo, HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'codigo', 'precio_compra', 'precio_venta', 'stock_actual', 'stock_minimo', 'activo', 'iva_pct'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('producto');
    }

    protected $fillable = [
        'empresa_id',
        'codigo',
        'codigo_barras',
        'nombre',
        'descripcion',
        'categoria_id',
        'unidad_medida_id',
        'precio_compra',
        'precio_venta',
        'precio_venta2',
        'precio_venta3',
        'iva_pct',
        'incluye_iva',
        'stock_minimo',
        'stock_maximo',
        'stock_actual',
        'ubicacion',
        'activo',
        'es_servicio',
        'imagen',
        'observaciones',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'precio_compra'   => 'decimal:2',
        'precio_venta'    => 'decimal:2',
        'precio_venta2'   => 'decimal:2',
        'precio_venta3'   => 'decimal:2',
        'iva_pct'         => 'decimal:2',
        'incluye_iva'     => 'boolean',
        'stock_minimo'    => 'decimal:2',
        'stock_maximo'    => 'decimal:4',
        'stock_actual'    => 'decimal:2',
        'activo'          => 'boolean',
        'es_servicio'     => 'boolean',
    ];

    /**
     * Proveedores del producto (many-to-many)
     */
    public function scopeBuscar($query, $texto)
    {
        return $query->where(function ($q) use ($texto) {
            $q->where('nombre',        'like', "%{$texto}%")
              ->orWhere('codigo',       'like', "%{$texto}%")
              ->orWhere('codigo_barras','like', "%{$texto}%");
        });
    }

    public function proveedores(): BelongsToMany
    {
        return $this->belongsToMany(Proveedor::class, 'producto_proveedor')
                    ->withPivot(['precio_compra_sugerido', 'proveedor_principal', 'created_at', 'updated_at'])
                    ->withTimestamps()
                    ->orderByPivot('proveedor_principal', 'desc');
    }

    /**
     * Proveedor principal del producto
     */
    public function proveedorPrincipal()
    {
        return $this->proveedores()->wherePivot('proveedor_principal', true)->first();
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function movimientosInventario()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function facturaItems()
    {
        return $this->hasMany(FacturaItem::class);
    }

    public function cotizacionItems()
    {
        return $this->hasMany(CotizacionItem::class);
    }

    public function remisionItems()
    {
        return $this->hasMany(RemisionItem::class);
    }

    public function ordenCompraItems()
    {
        return $this->hasMany(OrdenCompraItem::class);
    }

    public function notaCreditoItems()
    {
        return $this->hasMany(NotaCreditoItem::class);
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

