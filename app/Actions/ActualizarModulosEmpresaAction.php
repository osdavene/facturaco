<?php

namespace App\Actions;

use App\Models\Empresa;
use App\Models\Modulo;

class ActualizarModulosEmpresaAction
{
    public function execute(Empresa $empresa, array $moduloIds): void
    {
        $ids = collect($moduloIds)->map(fn($id) => (int) $id)->unique()->values();

        $validos = Modulo::where('activo', true)->whereIn('id', $ids)->pluck('id');

        $sync = [];
        foreach ($validos as $id) {
            $sync[$id] = ['activo' => true];
        }

        $empresa->modulos()->sync($sync);
    }
}
