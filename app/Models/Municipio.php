<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipio extends Model
{
    public $timestamps = false;
    protected $fillable = ['departamento_id', 'codigo', 'nombre'];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }
}
