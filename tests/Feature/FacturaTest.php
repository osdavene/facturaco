<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FacturaTest extends TestCase
{
    use RefreshDatabase;

    private User    $user;
    private Empresa $empresa;
    private Cliente $cliente;

    protected function setUp(): void
    {
        parent::setUp();
        [$this->user, $this->empresa] = $this->loginConEmpresa();

        $this->cliente = Cliente::factory()->create([
            'empresa_id'     => $this->empresa->id,
            'retefuente_pct' => 0,
            'reteiva_pct'    => 0,
            'reteica_pct'    => 0,
        ]);
    }

    private function itemsValidos(array $override = []): array
    {
        return [array_merge([
            'descripcion'     => 'Producto test',
            'cantidad'        => 2,
            'precio_unitario' => 100_000,
            'iva_pct'         => 19,
            'descuento_pct'   => 0,
            'unidad'          => 'UN',
            'codigo'          => 'TST-001',
        ], $override)];
    }

    private function sessionEmpresaActual(): array
    {
        return $this->sessionEmpresa($this->empresa);
    }

    // ── INDEX ──────────────────────────────────────────────────

    public function test_index_muestra_listado(): void
    {
        Factura::factory()->count(3)->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'user_id'    => $this->user->id,
        ]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('facturas.index'));

        $response->assertOk();
        $response->assertViewIs('facturas.index');
    }

    // ── STORE ──────────────────────────────────────────────────

    public function test_store_crea_factura_correctamente(): void
    {
        $response = $this->withSession($this->sessionEmpresaActual())
                         ->post(route('facturas.store'), [
                             'cliente_id'        => $this->cliente->id,
                             'fecha_emision'     => today()->toDateString(),
                             'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                             'estado'            => 'borrador',
                             'forma_pago'        => 'contado',
                             'items'             => $this->itemsValidos(),
                         ]);

        $response->assertRedirect(route('facturas.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('facturas', [
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'estado'     => 'borrador',
        ]);
    }

    public function test_store_calcula_totales_correctamente(): void
    {
        $this->withSession($this->sessionEmpresaActual())
             ->post(route('facturas.store'), [
                 'cliente_id'        => $this->cliente->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => $this->itemsValidos([
                     'cantidad'        => 2,
                     'precio_unitario' => 100_000,
                     'iva_pct'         => 19,
                     'descuento_pct'   => 0,
                 ]),
             ]);

        $factura = Factura::withoutGlobalScope('empresa')->latest()->first();

        // subtotal = 2 * 100k = 200k, iva = 38k, total = 238k
        $this->assertEquals(200_000, $factura->subtotal);
        $this->assertEquals(38_000,  $factura->iva);
        $this->assertEquals(238_000, $factura->total);
    }

    public function test_store_descuenta_stock_del_producto(): void
    {
        $producto = Producto::factory()->create([
            'empresa_id'   => $this->empresa->id,
            'stock_actual' => 50,
            'es_servicio'  => false,
        ]);

        $this->withSession($this->sessionEmpresaActual())
             ->post(route('facturas.store'), [
                 'cliente_id'        => $this->cliente->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => [[
                     'producto_id'     => $producto->id,
                     'descripcion'     => $producto->nombre,
                     'cantidad'        => 10,
                     'precio_unitario' => 100_000,
                     'iva_pct'         => 19,
                     'descuento_pct'   => 0,
                     'unidad'          => 'UN',
                     'codigo'          => $producto->codigo,
                 ]],
             ]);

        $this->assertEquals(40, $producto->fresh()->stock_actual);
    }

    public function test_store_falla_sin_items(): void
    {
        $response = $this->withSession($this->sessionEmpresaActual())
                         ->post(route('facturas.store'), [
                             'cliente_id'        => $this->cliente->id,
                             'fecha_emision'     => today()->toDateString(),
                             'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                             'items'             => [],
                         ]);

        $response->assertSessionHasErrors('items');
    }

    public function test_store_falla_sin_cliente(): void
    {
        $response = $this->withSession($this->sessionEmpresaActual())
                         ->post(route('facturas.store'), [
                             'fecha_emision'     => today()->toDateString(),
                             'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                             'items'             => $this->itemsValidos(),
                         ]);

        $response->assertSessionHasErrors('cliente_id');
    }

    // ── SHOW ───────────────────────────────────────────────────

    public function test_show_devuelve_vista_correcta(): void
    {
        $factura = Factura::factory()->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'user_id'    => $this->user->id,
        ]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('facturas.show', $factura));

        $response->assertOk();
        $response->assertViewIs('facturas.show');
        $response->assertViewHas('factura');
    }

    // ── CAMBIAR ESTADO ─────────────────────────────────────────

    public function test_cambiar_estado_actualiza_factura(): void
    {
        $factura = Factura::factory()->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'user_id'    => $this->user->id,
            'estado'     => 'emitida',
        ]);

        $this->withSession($this->sessionEmpresaActual())
             ->patch(route('facturas.estado', $factura), ['estado' => 'pagada']);

        $this->assertEquals('pagada', $factura->fresh()->estado);
    }

    public function test_cambiar_estado_rechaza_estado_invalido(): void
    {
        $factura = Factura::factory()->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'user_id'    => $this->user->id,
        ]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->patch(route('facturas.estado', $factura), ['estado' => 'inventado']);

        $response->assertSessionHasErrors('estado');
    }

    // ── DESTROY ────────────────────────────────────────────────

    public function test_destroy_anula_factura(): void
    {
        $factura = Factura::factory()->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'user_id'    => $this->user->id,
            'estado'     => 'emitida',
        ]);

        $this->withSession($this->sessionEmpresaActual())
             ->delete(route('facturas.destroy', $factura));

        $this->assertEquals('anulada', $factura->fresh()->estado);
    }
}
