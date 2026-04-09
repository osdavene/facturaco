<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnidadMedida extends Model
{
    use HasFactory;
    protected $table = 'unidades_medida';
    protected $fillable = ['nombre', 'abreviatura', 'activo'];
    protected $casts = ['activo' => 'boolean'];

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}