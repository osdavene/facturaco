<?php
namespace App\Models;

use App\Traits\PertenecerGrupo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UnidadMedida extends Model
{
    use HasFactory, PertenecerGrupo;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'empresa_id', 'nombre', 'abreviatura', 'activo', 'created_by', 'updated_by',
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
