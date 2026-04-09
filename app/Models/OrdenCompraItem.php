<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrdenCompraItem extends Model
{
    use HasFactory;

    protected $table = 'orden_compra_items';

    protected $fillable = [
        'orden_compra_id', 'producto_id',
        'codigo', 'descripcion', 'unidad',
        'cantidad', 'cantidad_recibida',
        'precio_unitario', 'descuento_pct', 'descuento',
        'subtotal', 'iva_pct', 'iva', 'total', 'orden',
    ];

    public function ordenCompra()
    {
        return $this->belongsTo(OrdenCompra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}