<?php

namespace Tests;

use App\Models\Empresa;
use App\Models\Modulo;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Session;

abstract class TestCase extends BaseTestCase
{
    /**
     * Carga roles, permisos y módulos antes de cada test.
     * Se ejecuta después de RefreshDatabase, por lo que la BD ya está limpia.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->seed(\Database\Seeders\ModuloSeeder::class);
    }

    /**
     * Crea empresa + usuario con rol propietario (acceso total),
     * activa todos los módulos en la empresa, vincula el pivot,
     * inicia sesión y pone empresa_activa_id en sesión.
     *
     * Retorna [$user, $empresa].
     */
    protected function loginConEmpresa(?User $user = null, ?Empresa $empresa = null): array
    {
        $empresa = $empresa ?? Empresa::factory()->create();
        $user    = $user    ?? User::factory()->create();

        // Rol propietario → Gate::before lo deja pasar todo
        $user->assignRole('propietario');

        // Pivot empresa_user
        $empresa->usuarios()->syncWithoutDetaching([
            $user->id => ['rol' => 'propietario', 'activo' => true],
        ]);

        // Activar todos los módulos disponibles para la empresa
        $modulos = Modulo::all();
        foreach ($modulos as $modulo) {
            $empresa->modulos()->syncWithoutDetaching([
                $modulo->id => ['activo' => true],
            ]);
        }

        // Sesión de empresa (necesario para los global scopes y el middleware)
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
