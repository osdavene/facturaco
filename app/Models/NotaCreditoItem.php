<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaCreditoItem extends Model
{
    public $timestamps = false;

    protected $table = 'nota_credito_items';

    protected $fillable = [
        'nota_credito_id', 'factura_item_id', 'producto_id',
        'codigo', 'descripcion', 'unidad',
        'cantidad', 'precio_unitario', 'subtotal',
        'iva_pct', 'iva', 'total',
        'devolver_stock', 'orden',
    ];

    protected $casts = [
        'devolver_stock' => 'boolean',
    ];

    public function notaCredito()
    {
        return $this->belongsTo(NotaCredito::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function facturaItem()
    {
        return $this->belongsTo(FacturaItem::class, 'factura_item_id');
    }
}
