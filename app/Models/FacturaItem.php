<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacturaItem extends Model
{
    use HasFactory;

    protected $table = 'factura_items';

    protected $fillable = [
        'factura_id', 'producto_id',
        'codigo', 'descripcion', 'unidad',
        'cantidad', 'precio_unitario',
        'descuento_pct', 'descuento', 'subtotal',
        'iva_pct', 'iva', 'total', 'orden',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}