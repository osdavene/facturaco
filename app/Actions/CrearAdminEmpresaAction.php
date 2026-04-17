<?php

namespace App\Actions;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CrearAdminEmpresaAction
{
    public function execute(array $data, Empresa $empresa): User
    {
        $user = User::create([
            'name'     => strtoupper($data['name']),
            'email'    => strtolower($data['email']),
            'password' => Hash::make($data['password']),
            'activo'   => true,
        ]);

        $user->assignRole($data['rol']);

        $rolPivot = in_array($data['rol'], ['admin', 'super-admin']) ? 'admin' : 'operador';

        $empresa->usuarios()->attach($user->id, ['rol' => $rolPivot, 'activo' => true]);

        // Si la empresa es filial, vincular también a la matriz
        if ($empresa->empresa_padre_id) {
            $matriz = $empresa->padre;
            if ($matriz && !$matriz->usuarios()->where('user_id', $user->id)->exists()) {
                $matriz->usuarios()->attach($user->id, ['rol' => $rolPivot, 'activo' => true]);
            }
        }

        // Si la empresa es matriz, vincular a todas sus filiales
        foreach ($empresa->filiales as $filial) {
            if (!$filial->usuarios()->where('user_id', $user->id)->exists()) {
                $filial->usuarios()->attach($user->id, ['rol' => $rolPivot, 'activo' => true]);
            }
        }

        return $user;
    }
}
