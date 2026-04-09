<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CotizacionItem extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_items';

    protected $fillable = [
        'cotizacion_id', 'producto_id',
        'codigo', 'descripcion', 'unidad',
        'cantidad', 'precio_unitario', 'descuento_pct', 'descuento',
        'subtotal', 'iva_pct', 'iva', 'total', 'orden',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}