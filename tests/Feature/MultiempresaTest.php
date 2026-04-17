<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica el aislamiento de datos entre empresas.
 *
 * Estos tests son los más críticos del sistema: si fallan, una empresa
 * puede ver o modificar datos de otra empresa.
 */
class MultiempresaTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresaA;
    private Empresa $empresaB;

    protected function setUp(): void
    {
        parent::setUp();

        [$userA, $this->empresaA] = $this->loginConEmpresa();
        [$userB, $this->empresaB] = $this->loginConEmpresa();

        // Volver a actuar como usuario de empresa A para los tests
        $this->actingAs($userA);
    }

    // ── CLIENTES ───────────────────────────────────────────────

    public function test_clientes_de_otra_empresa_no_aparecen_en_listado(): void
    {
        Cliente::factory()->count(4)->create(['empresa_id' => $this->empresaB->id]);
        Cliente::factory()->count(2)->create(['empresa_id' => $this->empresaA->id]);

        $response = $this->withSession($this->sessionEmpresa($this->empresaA))
                         ->get(route('clientes.index'));

        $response->assertOk();
        $response->assertViewHas('clientes', fn ($c) => $c->total() === 2);
    }

    public function test_cliente_de_otra_empresa_retorna_404(): void
    {
        $clienteB = Cliente::factory()->create(['empresa_id' => $this->empresaB->id]);

        $response = $this->withSession($this->sessionEmpresa($this->empresaA))
                         ->get(route('clientes.show', $clienteB));

        $response->assertNotFound();
    }

    public function test_no_puede_editar_cliente_de_otra_empresa(): void
    {
        $clienteB = Cliente::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'nombres'    => 'Original',
        ]);

        $this->withSession($this->sessionEmpresa($this->empresaA))
             ->put(route('clientes.update', $clienteB), [
                 'tipo_persona'     => 'natural',
                 'tipo_documento'   => 'CC',
                 'numero_documento' => '9999999999',
                 'nombres'          => 'Hackeado',
                 'apellidos'        => 'Test',
             ]);

        $this->assertEquals('Original', $clienteB->fresh()->nombres);
    }

    public function test_no_puede_eliminar_cliente_de_otra_empresa(): void
    {
        $clienteB = Cliente::factory()->create(['empresa_id' => $this->empresaB->id]);

        $this->withSession($this->sessionEmpresa($this->empresaA))
             ->delete(route('clientes.destroy', $clienteB));

        $this->assertDatabaseHas('clientes', [
            'id'         => $clienteB->id,
            'deleted_at' => null,
        ]);
    }

    // ── FACTURAS ───────────────────────────────────────────────

    public function test_facturas_de_otra_empresa_no_aparecen_en_listado(): void
    {
        $clienteB = Cliente::factory()->create(['empresa_id' => $this->empresaB->id]);
        Factura::factory()->count(5)->create(['empresa_id' => $this->empresaB->id, 'cliente_id' => $clienteB->id]);

        $clienteA = Cliente::factory()->create(['empresa_id' => $this->empresaA->id]);
        Factura::factory()->count(3)->create(['empresa_id' => $this->empresaA->id, 'cliente_id' => $clienteA->id]);

        $response = $this->withSession($this->sessionEmpresa($this->empresaA))
                         ->get(route('facturas.index'));

        $response->assertOk();
        $response->assertViewHas('facturas', fn ($f) => $f->total() === 3);
    }

    public function test_factura_de_otra_empresa_retorna_404(): void
    {
        $clienteB  = Cliente::factory()->create(['empresa_id' => $this->empresaB->id]);
        $facturaB  = Factura::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'cliente_id' => $clienteB->id,
        ]);

        $response = $this->withSession($this->sessionEmpresa($this->empresaA))
                         ->get(route('facturas.show', $facturaB));

        $response->assertNotFound();
    }

    public function test_no_puede_anular_factura_de_otra_empresa(): void
    {
        $clienteB = Cliente::factory()->create(['empresa_id' => $this->empresaB->id]);
        $facturaB = Factura::factory()->create([
            'empresa_id' => $this->empresaB->id,
            'cliente_id' => $clienteB->id,
            'estado'     => 'emitida',
        ]);

        $this->withSession($this->sessionEmpresa($this->empresaA))
             ->delete(route('facturas.destroy', $facturaB));

        $this->assertNotEquals('anulada', $facturaB->fresh()->estado);
    }

    // ── PRODUCTOS ──────────────────────────────────────────────

    public function test_productos_de_otra_empresa_no_aparecen_en_listado(): void
    {
        Producto::factory()->count(6)->create(['empresa_id' => $this->empresaB->id]);
        Producto::factory()->count(2)->create(['empresa_id' => $this->empresaA->id]);

        $response = $this->withSession($this->sessionEmpresa($this->empresaA))
                         ->get(route('inventario.index'));

        $response->assertOk();
        $response->assertViewHas('productos', fn ($p) => $p->total() === 2);
    }

    public function test_producto_de_otra_empresa_retorna_404(): void
    {
        $productoB = Producto::factory()->create(['empresa_id' => $this->empresaB->id]);

        $response = $this->withSession($this->sessionEmpresa($this->empresaA))
                         ->get(route('inventario.show', $productoB));

        $response->assertNotFound();
    }

    // ── SESIÓN DE EMPRESA ──────────────────────────────────────

    public function test_cambiar_sesion_a_empresa_b_muestra_solo_sus_datos(): void
    {
        $clienteA = Cliente::factory()->create(['empresa_id' => $this->empresaA->id]);
        $clienteB = Cliente::factory()->create(['empresa_id' => $this->empresaB->id]);

        // Con sesión de empresa A → solo ve clienteA
        $responseA = $this->withSession($this->sessionEmpresa($this->empresaA))
                          ->get(route('clientes.index'));
        $responseA->assertViewHas('clientes', fn ($c) => $c->total() === 1);

        // Con sesión de empresa B → solo ve clienteB
        $responseB = $this->withSession($this->sessionEmpresa($this->empresaB))
                          ->get(route('clientes.index'));
        $responseB->assertViewHas('clientes', fn ($c) => $c->total() === 1);
    }
}
