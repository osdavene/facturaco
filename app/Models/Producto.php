<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'codigo', 'codigo_barras', 'nombre', 'descripcion',
        'categoria_id', 'unidad_medida_id',
        'precio_compra', 'precio_venta', 'precio_venta2', 'precio_venta3',
        'iva_pct', 'incluye_iva',
        'stock_actual', 'stock_minimo', 'stock_maximo', 'ubicacion',
        'activo', 'es_servicio', 'imagen', 'observaciones', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'incluye_iva' => 'boolean',
        'activo'      => 'boolean',
        'es_servicio' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function getBajoStockAttribute(): bool
    {
        return $this->stock_actual <= $this->stock_minimo && !$this->es_servicio;
    }

    public function scopeBuscar($query, $texto)
    {
        return $query->where(function($q) use ($texto) {
            $q->where('nombre',         'like', "%{$texto}%")
              ->orWhere('codigo',        'like', "%{$texto}%")
              ->orWhere('codigo_barras', 'like', "%{$texto}%");
        });
    }

    public function creadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

}