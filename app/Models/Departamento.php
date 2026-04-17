<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    public $timestamps = false;
    protected $fillable = ['codigo', 'nombre'];

    public function municipios()
    {
        return $this->hasMany(Municipio::class)->orderBy('nombre');
    }
}
