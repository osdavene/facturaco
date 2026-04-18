<?php

namespace App\Models;

use App\Traits\PertenecerGrupo;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use PertenecerGrupo, HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'codigo',
        'nombre',
        'descripcion',
        'categoria_id',
        'unidad_medida_id',
        'precio_compra',
        'precio_venta',
        'stock_minimo',
        'stock_actual',
        'activo',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'precio_compra'   => 'decimal:2',
        'precio_venta'    => 'decimal:2',
        'stock_minimo'    => 'decimal:2',
        'stock_actual'    => 'decimal:2',
        'activo'          => 'boolean',
    ];

    /**
     * Proveedores del producto (many-to-many)
     */
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

