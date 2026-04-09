<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RemisionItem extends Model
{
    use HasFactory;

    protected $table = 'remision_items';

    protected $fillable = [
        'remision_id', 'producto_id',
        'codigo', 'descripcion', 'unidad',
        'cantidad', 'precio_unitario', 'total', 'orden',
    ];

    public function remision() { return $this->belongsTo(Remision::class); }
    public function producto() { return $this->belongsTo(Producto::class); }
}