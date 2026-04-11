<?php

namespace Tests\Unit\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class InventarioServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventarioService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InventarioService();
    }

    private function crearProducto(array $attrs = []): Producto
    {
        [$user, $empresa] = $this->loginConEmpresa();

        return Producto::factory()->create(array_merge(
            ['empresa_id' => $empresa->id, 'stock_actual' => 50],
            $attrs,
        ));
    }

    // ── registrarSalida ────────────────────────────────────────

    public function test_salida_descuenta_stock(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 50]);
        $userId   = auth()->id();

        $this->service->registrarSalida($producto, 10, 'FE-2026-0001', $userId);

        $this->assertEquals(40, $producto->fresh()->stock_actual);
    }

    public function test_salida_crea_movimiento_en_bd(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 50]);
        $userId   = auth()->id();

        $this->service->registrarSalida($producto, 10, 'FE-2026-0001', $userId, 'Venta');

        $movimiento = MovimientoInventario::where('producto_id', $producto->id)->first();

        $this->assertNotNull($movimiento);
        $this->assertEquals('salida',       $movimiento->tipo);
        $this->assertEquals(10,             $movimiento->cantidad);
        $this->assertEquals(50,             $movimiento->stock_anterior);
        $this->assertEquals(40,             $movimiento->stock_nuevo);
        $this->assertEquals('Venta',        $movimiento->motivo);
        $this->assertEquals('FE-2026-0001', $movimiento->referencia);
    }

    public function test_salida_no_afecta_servicios(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 0, 'es_servicio' => true]);
        $userId   = auth()->id();

        $this->service->registrarSalida($producto, 5, 'FE-2026-0001', $userId);

        $this->assertEquals(0, $producto->fresh()->stock_actual);
        $this->assertEquals(0, MovimientoInventario::count());
    }

    // ── registrarEntrada ───────────────────────────────────────

    public function test_entrada_incrementa_stock(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 20]);
        $userId   = auth()->id();

        $this->service->registrarEntrada($producto, 30, 'OC-2026-0001', $userId);

        $this->assertEquals(50, $producto->fresh()->stock_actual);
    }

    public function test_entrada_actualiza_precio_compra_si_se_provee(): void
    {
        $producto = $this->crearProducto(['precio_compra' => 10_000]);
        $userId   = auth()->id();

        $this->service->registrarEntrada($producto, 10, 'OC-2026-0001', $userId, costoUnitario: 15_000);

        $this->assertEquals(15_000, $producto->fresh()->precio_compra);
    }

    public function test_entrada_no_cambia_precio_si_costo_es_cero(): void
    {
        $producto = $this->crearProducto(['precio_compra' => 10_000]);
        $userId   = auth()->id();

        $this->service->registrarEntrada($producto, 10, 'OC-2026-0001', $userId, costoUnitario: 0);

        $this->assertEquals(10_000, $producto->fresh()->precio_compra);
    }

    public function test_entrada_crea_movimiento_tipo_entrada(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 20]);
        $userId   = auth()->id();

        $this->service->registrarEntrada($producto, 30, 'OC-2026-0001', $userId, 'Recepción OC', 12_000);

        $movimiento = MovimientoInventario::where('producto_id', $producto->id)->first();

        $this->assertNotNull($movimiento);
        $this->assertEquals('entrada',      $movimiento->tipo);
        $this->assertEquals(30,             $movimiento->cantidad);
        $this->assertEquals(20,             $movimiento->stock_anterior);
        $this->assertEquals(50,             $movimiento->stock_nuevo);
        $this->assertEquals(12_000,         $movimiento->costo_unitario);
        $this->assertEquals('Recepción OC', $movimiento->motivo);
    }

    public function test_entrada_no_afecta_servicios(): void
    {
        $producto = $this->crearProducto(['stock_actual' => 0, 'es_servicio' => true]);
        $userId   = auth()->id();

        $this->service->registrarEntrada($producto, 100, 'OC-2026-0001', $userId);

        $this->assertEquals(0, $producto->fresh()->stock_actual);
        $this->assertEquals(0, MovimientoInventario::count());
    }
}
