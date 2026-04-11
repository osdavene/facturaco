<?php

namespace Tests\Unit\Services;

use App\Services\DocumentoService;
use PHPUnit\Framework\TestCase;

class DocumentoServiceTest extends TestCase
{
    private DocumentoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentoService();
    }

    // ── calcularItems ──────────────────────────────────────────

    public function test_item_simple_sin_descuento(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 2, 'precio_unitario' => 100_000, 'iva_pct' => 19],
        ]);

        $this->assertEquals(200_000, $resultado['subtotal']);
        $this->assertEquals(0,       $resultado['descuento']);
        $this->assertEquals(38_000,  $resultado['iva']);
        $this->assertEquals(238_000, $resultado['total']);
    }

    public function test_item_con_descuento(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 1, 'precio_unitario' => 100_000, 'descuento_pct' => 10, 'iva_pct' => 19],
        ]);

        // base = 100000 - 10% = 90000
        // iva  = 90000 * 19% = 17100
        $this->assertEquals(90_000,  $resultado['subtotal']);
        $this->assertEquals(10_000,  $resultado['descuento']);
        $this->assertEquals(17_100,  $resultado['iva']);
        $this->assertEquals(107_100, $resultado['total']);
    }

    public function test_item_sin_iva(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 3, 'precio_unitario' => 50_000, 'iva_pct' => 0],
        ]);

        $this->assertEquals(150_000, $resultado['subtotal']);
        $this->assertEquals(0,       $resultado['iva']);
        $this->assertEquals(150_000, $resultado['total']);
    }

    public function test_multiples_items_acumula_correctamente(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 1, 'precio_unitario' => 100_000, 'iva_pct' => 19], // base 100k, iva 19k
            ['cantidad' => 2, 'precio_unitario' =>  50_000, 'iva_pct' =>  0], // base 100k, iva 0
        ]);

        $this->assertEquals(200_000, $resultado['subtotal']);
        $this->assertEquals(19_000,  $resultado['iva']);
        $this->assertEquals(219_000, $resultado['total']);
    }

    public function test_items_calculados_tienen_campos_correctos(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 2, 'precio_unitario' => 100_000, 'iva_pct' => 19, 'descripcion' => 'Producto A'],
        ]);

        $item = $resultado['items'][0];
        $this->assertArrayHasKey('subtotal',        $item);
        $this->assertArrayHasKey('iva',             $item);
        $this->assertArrayHasKey('total',           $item);
        $this->assertArrayHasKey('descuento',       $item);
        $this->assertArrayHasKey('descuento_pct',   $item);
        $this->assertArrayHasKey('orden',           $item);
        $this->assertEquals('Producto A', $item['descripcion']);
        $this->assertEquals(0, $item['orden']);
    }

    public function test_orden_asignado_por_posicion(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 1, 'precio_unitario' => 10_000],
            ['cantidad' => 1, 'precio_unitario' => 20_000],
            ['cantidad' => 1, 'precio_unitario' => 30_000],
        ]);

        $this->assertEquals(0, $resultado['items'][0]['orden']);
        $this->assertEquals(1, $resultado['items'][1]['orden']);
        $this->assertEquals(2, $resultado['items'][2]['orden']);
    }

    public function test_descuento_pct_por_defecto_es_cero(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 1, 'precio_unitario' => 100_000],
        ]);

        $this->assertEquals(0, $resultado['items'][0]['descuento_pct']);
        $this->assertEquals(0, $resultado['items'][0]['descuento']);
    }

    public function test_iva_pct_por_defecto_es_19(): void
    {
        $resultado = $this->service->calcularItems([
            ['cantidad' => 1, 'precio_unitario' => 100_000],
        ]);

        $this->assertEquals(19,     $resultado['items'][0]['iva_pct']);
        $this->assertEquals(19_000, $resultado['items'][0]['iva']);
    }

    // ── calcularRetenciones ────────────────────────────────────

    public function test_retenciones_sin_porcentajes(): void
    {
        $ret = $this->service->calcularRetenciones(
            subtotal:      100_000,
            iva:            19_000,
            retefuentePct: 0,
            reteivaPct:    0,
            reteicaPct:    0,
        );

        $this->assertEquals(0,       $ret['retefuente']);
        $this->assertEquals(0,       $ret['reteiva']);
        $this->assertEquals(0,       $ret['reteica']);
        $this->assertEquals(119_000, $ret['total_neto']);
    }

    public function test_retenciones_con_todos_los_porcentajes(): void
    {
        // subtotal=100k, iva=19k, rete=3.5%, reteiva=15%, reteica=0.414%
        $ret = $this->service->calcularRetenciones(
            subtotal:      100_000,
            iva:            19_000,
            retefuentePct: 3.5,
            reteivaPct:    15,
            reteicaPct:    0.414,
        );

        // Usamos delta de 1 COP por precisión de punto flotante
        $this->assertEqualsWithDelta(3_500,   $ret['retefuente'], 1); // 100k * 3.5%
        $this->assertEqualsWithDelta(2_850,   $ret['reteiva'],    1); // 19k  * 15%
        $this->assertEqualsWithDelta(414,     $ret['reteica'],    1); // 100k * 0.414%
        $this->assertEqualsWithDelta(112_236, $ret['total_neto'], 1); // 119k - 3500 - 2850 - 414
    }

    public function test_retefuente_100_pct_da_total_neto_cero(): void
    {
        // subtotal=10k, iva=0, rete=100% → retefuente=10k, total_neto=10k+0-10k=0
        $ret = $this->service->calcularRetenciones(
            subtotal:      10_000,
            iva:               0,
            retefuentePct: 100,
            reteivaPct:      0,
            reteicaPct:      0,
        );

        $this->assertEquals(10_000, $ret['retefuente']);
        $this->assertEquals(0,      $ret['total_neto']);
    }
}
