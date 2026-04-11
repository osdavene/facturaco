<?php

namespace Tests;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Session;

abstract class TestCase extends BaseTestCase
{
    /**
     * Crea empresa + usuario, vincula el pivot, inicia sesión
     * y pone empresa_activa_id en sesión.
     *
     * Retorna [$user, $empresa] para que el test pueda crear datos relacionados.
     */
    protected function loginConEmpresa(?User $user = null, ?Empresa $empresa = null): array
    {
        $empresa = $empresa ?? Empresa::factory()->create();
        $user    = $user    ?? User::factory()->create();

        // Pivot empresa_user
        $empresa->usuarios()->syncWithoutDetaching([
            $user->id => ['rol' => 'admin', 'activo' => true],
        ]);

        // Sesión de empresa (necesario para los global scopes)
        Session::put('empresa_activa_id', $empresa->id);
        Session::put('empresa_raiz_id',   $empresa->id);
        Session::put('empresa_grupo_ids', [$empresa->id]);

        $this->actingAs($user);

        return [$user, $empresa];
    }

    /**
     * Devuelve el array de sesión de empresa para pasarlo en withSession().
     */
    protected function sessionEmpresa(Empresa $empresa): array
    {
        return [
            'empresa_activa_id' => $empresa->id,
            'empresa_raiz_id'   => $empresa->id,
            'empresa_grupo_ids' => [$empresa->id],
        ];
    }
}
