<?php
namespace App\Models;

use App\Traits\PertenecerEmpresa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Categoria extends Model
{
    use HasFactory, PertenecerEmpresa;

    protected $table = 'categorias';

    protected $fillable = [
        'empresa_id', 'nombre', 'descripcion', 'activo', 'created_by', 'updated_by',
    ];

    protected $casts = ['activo' => 'boolean'];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }

    public function creadoPor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizadoPor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
