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

    // ── UPDATE ─────────────────────────────────────────────────

    public function test_update_recalcula_stock_al_cambiar_cantidad(): void
    {
        $producto = Producto::factory()->create([
            'empresa_id'   => $this->empresa->id,
            'stock_actual' => 100,
            'es_servicio'  => false,
        ]);

        // Crear factura que descuenta 10 unidades → stock queda en 90
        $this->withSession($this->sessionEmpresaActual())
             ->post(route('facturas.store'), [
                 'cliente_id'        => $this->cliente->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => [[
                     'producto_id'     => $producto->id,
                     'descripcion'     => $producto->nombre,
                     'cantidad'        => 10,
                     'precio_unitario' => 50_000,
                     'iva_pct'         => 19,
                     'descuento_pct'   => 0,
                     'unidad'          => 'UN',
                     'codigo'          => $producto->codigo ?? 'TST',
                 ]],
             ]);

        $this->assertEquals(90, $producto->fresh()->stock_actual);

        $factura = Factura::withoutGlobalScope('empresa')->latest()->first();

        // Editar la factura cambiando cantidad a 20 → debe revertir 10 y descontar 20 → stock = 80
        $this->withSession($this->sessionEmpresaActual())
             ->put(route('facturas.update', $factura), [
                 'cliente_id'        => $this->cliente->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => [[
                     'producto_id'     => $producto->id,
                     'descripcion'     => $producto->nombre,
                     'cantidad'        => 20,
                     'precio_unitario' => 50_000,
                     'iva_pct'         => 19,
                     'descuento_pct'   => 0,
                     'unidad'          => 'UN',
                     'codigo'          => $producto->codigo ?? 'TST',
                 ]],
             ]);

        $this->assertEquals(80, $producto->fresh()->stock_actual);
    }

    // ── RETENCIONES ────────────────────────────────────────────

    public function test_store_aplica_retefuente_del_cliente(): void
    {
        $clienteConRete = Cliente::factory()->conRetenciones()->create([
            'empresa_id'     => $this->empresa->id,
            'retefuente_pct' => 3.5,
            'reteiva_pct'    => 0,
            'reteica_pct'    => 0,
        ]);

        $this->withSession($this->sessionEmpresaActual())
             ->post(route('facturas.store'), [
                 'cliente_id'        => $clienteConRete->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => $this->itemsValidos([
                     'cantidad'        => 1,
                     'precio_unitario' => 1_000_000,
                     'iva_pct'         => 19,
                     'descuento_pct'   => 0,
                 ]),
             ]);

        $factura = Factura::withoutGlobalScope('empresa')->latest()->first();

        // subtotal = 1.000.000, retefuente = 3.5% = 35.000
        $this->assertEquals(35_000, $factura->retefuente);
        // total = subtotal + iva - retefuente = 1.000.000 + 190.000 - 35.000 = 1.155.000
        $this->assertEquals(1_155_000, $factura->total);
    }

    public function test_store_aplica_multiples_retenciones(): void
    {
        $clienteConRete = Cliente::factory()->create([
            'empresa_id'     => $this->empresa->id,
            'retefuente_pct' => 3.5,
            'reteiva_pct'    => 15,
            'reteica_pct'    => 0,
        ]);

        $this->withSession($this->sessionEmpresaActual())
             ->post(route('facturas.store'), [
                 'cliente_id'        => $clienteConRete->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => $this->itemsValidos([
                     'cantidad'        => 1,
                     'precio_unitario' => 1_000_000,
                     'iva_pct'         => 19,
                     'descuento_pct'   => 0,
                 ]),
             ]);

        $factura = Factura::withoutGlobalScope('empresa')->latest()->first();

        // retefuente = 3.5% de 1.000.000 = 35.000
        $this->assertEquals(35_000, $factura->retefuente);
        // reteiva = 15% del IVA (190.000) = 28.500
        $this->assertEquals(28_500, $factura->reteiva);
        // total = 1.000.000 + 190.000 - 35.000 - 28.500 = 1.126.500
        $this->assertEquals(1_126_500, $factura->total);
    }

    // ── AISLAMIENTO MULTI-EMPRESA ──────────────────────────────

    public function test_no_ve_facturas_de_otra_empresa(): void
    {
        $otraEmpresa  = Empresa::factory()->create();
        $otroCliente  = Cliente::factory()->create(['empresa_id' => $otraEmpresa->id]);

        Factura::factory()->count(5)->create([
            'empresa_id' => $otraEmpresa->id,
            'cliente_id' => $otroCliente->id,
        ]);
        Factura::factory()->count(2)->create([
            'empresa_id' => $this->empresa->id,
            'cliente_id' => $this->cliente->id,
            'user_id'    => $this->user->id,
        ]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('facturas.index'));

        $response->assertViewHas('facturas', function ($facturas) {
            return $facturas->total() === 2;
        });
    }

    public function test_no_puede_ver_factura_de_otra_empresa(): void
    {
        $otraEmpresa = Empresa::factory()->create();
        $otroCliente = Cliente::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $otraFactura = Factura::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'cliente_id' => $otroCliente->id,
        ]);

        $response = $this->withSession($this->sessionEmpresaActual())
                         ->get(route('facturas.show', $otraFactura));

        // El global scope de empresa debe retornar 404 (modelo no encontrado)
        $response->assertNotFound();
    }

    public function test_no_puede_eliminar_factura_de_otra_empresa(): void
    {
        $otraEmpresa = Empresa::factory()->create();
        $otroCliente = Cliente::factory()->create(['empresa_id' => $otraEmpresa->id]);
        $otraFactura = Factura::factory()->create([
            'empresa_id' => $otraEmpresa->id,
            'cliente_id' => $otroCliente->id,
            'estado'     => 'emitida',
        ]);

        $this->withSession($this->sessionEmpresaActual())
             ->delete(route('facturas.destroy', $otraFactura));

        // La factura de la otra empresa no debe haber sido anulada
        $this->assertNotEquals('anulada', $otraFactura->fresh()->estado);
    }

    // ── CONSECUTIVOS ───────────────────────────────────────────

    public function test_consecutivos_son_independientes_por_empresa(): void
    {
        // Empresa A crea su primera factura
        $this->withSession($this->sessionEmpresaActual())
             ->post(route('facturas.store'), [
                 'cliente_id'        => $this->cliente->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => $this->itemsValidos(),
             ]);

        $facturaEmpresaA = Factura::withoutGlobalScope('empresa')
            ->where('empresa_id', $this->empresa->id)
            ->latest()->first();

        // Empresa B crea su primera factura
        [$userB, $empresaB] = $this->loginConEmpresa();
        $clienteB = Cliente::factory()->create(['empresa_id' => $empresaB->id]);

        $this->withSession($this->sessionEmpresa($empresaB))
             ->post(route('facturas.store'), [
                 'cliente_id'        => $clienteB->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => $this->itemsValidos(),
             ]);

        $facturaEmpresaB = Factura::withoutGlobalScope('empresa')
            ->where('empresa_id', $empresaB->id)
            ->latest()->first();

        // Ambas deben tener consecutivo 1 (independientes)
        $this->assertEquals(1, $facturaEmpresaA->consecutivo);
        $this->assertEquals(1, $facturaEmpresaB->consecutivo);

        // Los números de factura deben ser distintos (diferente empresa o mismo con empresa distinta)
        $this->assertNotEquals($facturaEmpresaA->empresa_id, $facturaEmpresaB->empresa_id);
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

    public function test_destroy_revierte_stock_al_anular(): void
    {
        $producto = Producto::factory()->create([
            'empresa_id'   => $this->empresa->id,
            'stock_actual' => 100,
            'es_servicio'  => false,
        ]);

        // Crear factura que descuenta 15 unidades
        $this->withSession($this->sessionEmpresaActual())
             ->post(route('facturas.store'), [
                 'cliente_id'        => $this->cliente->id,
                 'fecha_emision'     => today()->toDateString(),
                 'fecha_vencimiento' => today()->addDays(30)->toDateString(),
                 'items'             => [[
                     'producto_id'     => $producto->id,
                     'descripcion'     => $producto->nombre,
                     'cantidad'        => 15,
                     'precio_unitario' => 50_000,
                     'iva_pct'         => 0,
                     'descuento_pct'   => 0,
                     'unidad'          => 'UN',
                     'codigo'          => $producto->codigo ?? 'TST',
                 ]],
             ]);

        $this->assertEquals(85, $producto->fresh()->stock_actual);

        $factura = Factura::withoutGlobalScope('empresa')->latest()->first();

        // Anular la factura → debe revertir el stock a 100
        $this->withSession($this->sessionEmpresaActual())
             ->delete(route('facturas.destroy', $factura));

        $this->assertEquals(100, $producto->fresh()->stock_actual);
    }
}
