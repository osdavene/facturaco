<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Modulo;
use App\Models\User;
use App\Models\Factura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Verifica que los permisos por rol y por módulo se aplican correctamente.
 *
 * Cada empresa activa sus módulos; dentro de cada módulo, los roles
 * controlan qué acciones puede ejecutar cada usuario.
 */
class PermisosModuloTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;

    protected function setUp(): void
    {
        parent::setUp();

        [, $this->empresa] = $this->loginConEmpresa();
    }

    private function usuarioConRol(string $rol): User
    {
        $user = User::factory()->create();
        $this->empresa->usuarios()->syncWithoutDetaching([
            $user->id => ['rol' => $rol, 'activo' => true],
        ]);
        $user->assignRole($rol);
        return $user;
    }

    // ── PROPIETARIO ────────────────────────────────────────────

    public function test_propietario_puede_ver_facturas(): void
    {
        $user = $this->usuarioConRol('propietario');

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($this->empresa))
                         ->get(route('facturas.index'));

        $response->assertOk();
    }

    public function test_propietario_puede_anular_facturas(): void
    {
        $user    = $this->usuarioConRol('propietario');
        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);
        $factura = \App\Models\Factura::factory()->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'estado'     => 'emitida',
        ]);

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($this->empresa))
                         ->delete(route('facturas.destroy', $factura));

        $response->assertRedirect();
        $this->assertEquals('anulada', $factura->fresh()->estado);
    }

    // ── VENDEDOR ───────────────────────────────────────────────

    public function test_vendedor_puede_ver_facturas(): void
    {
        $user = $this->usuarioConRol('vendedor');

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($this->empresa))
                         ->get(route('facturas.index'));

        $response->assertOk();
    }

    public function test_vendedor_no_puede_anular_facturas(): void
    {
        $user    = $this->usuarioConRol('vendedor');
        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);
        $factura = \App\Models\Factura::factory()->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $cliente->id,
            'estado'     => 'emitida',
        ]);

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($this->empresa))
                         ->delete(route('facturas.destroy', $factura));

        $response->assertForbidden();
        $this->assertNotEquals('anulada', $factura->fresh()->estado);
    }

    // ── SOLO LECTURA ───────────────────────────────────────────

    public function test_solo_lectura_puede_ver_facturas(): void
    {
        $user = $this->usuarioConRol('solo-lectura');

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($this->empresa))
                         ->get(route('facturas.index'));

        $response->assertOk();
    }

    public function test_solo_lectura_no_puede_crear_facturas(): void
    {
        $user    = $this->usuarioConRol('solo-lectura');
        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($this->empresa))
                         ->post(route('facturas.store'), [
                             'cliente_id'        => $cliente->id,
                             'fecha_emision'     => today()->toDateString(),
                             'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                             'items'             => [[
                                 'descripcion'     => 'Test',
                                 'cantidad'        => 1,
                                 'precio_unitario' => 100_000,
                                 'iva_pct'         => 19,
                                 'descuento_pct'   => 0,
                                 'unidad'          => 'UN',
                             ]],
                         ]);

        $response->assertForbidden();
    }

    // ── MÓDULO DESACTIVADO ─────────────────────────────────────

    public function test_modulo_desactivado_bloquea_acceso(): void
    {
        // Desactivar el módulo de facturación para la empresa
        $modulo = Modulo::where('slug', 'facturacion')->first();
        $this->empresa->modulos()->updateExistingPivot($modulo->id, ['activo' => false]);

        $user = $this->usuarioConRol('propietario');

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($this->empresa))
                         ->get(route('facturas.index'));

        $response->assertForbidden();
    }

    public function test_modulo_no_asignado_bloquea_acceso(): void
    {
        // Empresa sin módulo de facturación
        $empresa2 = Empresa::factory()->create();
        $user     = $this->usuarioConRol('propietario');
        $empresa2->usuarios()->syncWithoutDetaching([$user->id => ['rol' => 'propietario', 'activo' => true]]);

        $response = $this->actingAs($user)
                         ->withSession($this->sessionEmpresa($empresa2))
                         ->get(route('facturas.index'));

        $response->assertForbidden();
    }
}
