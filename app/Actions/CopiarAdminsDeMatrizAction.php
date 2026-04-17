<?php

namespace App\Actions;

use App\Models\Empresa;

class CopiarAdminsDeMatrizAction
{
    public function execute(Empresa $filial): void
    {
        $matriz = Empresa::find($filial->empresa_padre_id);
        if (!$matriz) return;

        $admins = $matriz->usuarios()->wherePivot('rol', 'admin')->get();
        foreach ($admins as $admin) {
            if (!$filial->usuarios()->where('user_id', $admin->id)->exists()) {
                $filial->usuarios()->attach($admin->id, ['rol' => 'admin', 'activo' => true]);
            }
        }
    }
}
