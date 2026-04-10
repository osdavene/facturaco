<?php
namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoInventario extends Model
{
    use HasFactory, PertenecerEmpresa;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'empresa_id',
        'producto_id', 'tipo', 'cantidad',
        'stock_anterior', 'stock_nuevo',
        'costo_unitario', 'motivo', 'referencia', 'user_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
