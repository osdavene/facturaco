<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnidadMedida extends Model
{
    use HasFactory;
    protected $table = 'unidades_medida';
    protected $fillable = ['nombre', 'abreviatura', 'activo', 'created_by', 'updated_by'];
    protected $casts = ['activo' => 'boolean'];

    public function productos()
    {
        return $this->hasMany(Producto::class);
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