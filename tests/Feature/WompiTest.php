<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WompiTest extends TestCase
{
    use RefreshDatabase;

    private Empresa $empresa;
    private Factura $factura;

    protected function setUp(): void
    {
        parent::setUp();

        [, $this->empresa] = $this->loginConEmpresa();

        $cliente = Cliente::factory()->create(['empresa_id' => $this->empresa->id]);

        $this->factura = Factura::factory()->create([
            'empresa_id'   => $this->empresa->id,
            'cliente_id'   => $cliente->id,
            'numero'       => 'FE-2026-0001',
            'estado'       => 'emitida',
            'total'        => 500_000,
            'total_pagado' => 0,
        ]);
    }

    private function payload(array $override = []): array
    {
        $reference = "FCO-{$this->empresa->id}-{$this->factura->numero}";

        $base = [
            'event'     => 'transaction.updated',
            'timestamp' => time(),
            'data'      => [
                'transaction' => [
                    'id'              => 'txn_test_123',
                    'status'          => 'APPROVED',
                    'reference'       => $reference,
                    'amount_in_cents' => 50_000_000,
                ],
            ],
            'signature' => [
                'properties' => ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'],
                'checksum'   => 'ignorado-sin-events-key',
            ],
        ];

        foreach ($override as $key => $value) {
            if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
                $base[$key] = array_replace_recursive($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    // ── PAGO APROBADO ──────────────────────────────────────────

    public function test_webhook_approved_marca_factura_como_pagada(): void
    {
        $response = $this->postJson(route('wompi.webhook'), $this->payload());

        $response->assertOk();
        $response->assertJson(['ok' => true]);

        $this->assertEquals('pagada', $this->factura->fresh()->estado);
        $this->assertEquals(500_000, $this->factura->fresh()->total_pagado);
    }

    public function test_webhook_pago_parcial_no_marca_como_pagada(): void
    {
        // Pago parcial: $300.000 de una factura de $500.000
        $payload = $this->payload([
            'data' => ['transaction' => ['amount_in_cents' => 30_000_000]],
        ]);

        $response = $this->postJson(route('wompi.webhook'), $payload);

        $response->assertOk();

        $facturaActualizada = $this->factura->fresh();
        $this->assertEquals(300_000, $facturaActualizada->total_pagado);
        $this->assertNotEquals('pagada', $facturaActualizada->estado);
    }

    // ── ESTADOS NO APROBADOS ───────────────────────────────────

    public function test_webhook_declined_no_cambia_estado(): void
    {
        $payload = $this->payload([
            'data' => ['transaction' => ['status' => 'DECLINED']],
        ]);

        $this->postJson(route('wompi.webhook'), $payload);

        $this->assertEquals('emitida', $this->factura->fresh()->estado);
        $this->assertEquals(0, $this->factura->fresh()->total_pagado);
    }

    public function test_webhook_voided_no_cambia_estado(): void
    {
        $payload = $this->payload([
            'data' => ['transaction' => ['status' => 'VOIDED']],
        ]);

        $this->postJson(route('wompi.webhook'), $payload);

        $this->assertEquals('emitida', $this->factura->fresh()->estado);
    }

    // ── EVENTOS NO RELEVANTES ──────────────────────────────────

    public function test_webhook_ignora_eventos_que_no_son_transaction_updated(): void
    {
        $payload          = $this->payload();
        $payload['event'] = 'payment_link.paid';

        $response = $this->postJson(route('wompi.webhook'), $payload);

        $response->assertOk();
        $this->assertEquals('emitida', $this->factura->fresh()->estado);
    }

    // ── REFERENCIA MALFORMADA ──────────────────────────────────

    public function test_webhook_referencia_sin_formato_fco_retorna_ok_sin_cambios(): void
    {
        $payload = $this->payload([
            'data' => ['transaction' => ['reference' => 'REF-OTRO-SISTEMA-123']],
        ]);

        $response = $this->postJson(route('wompi.webhook'), $payload);

        $response->assertOk();
        $this->assertEquals('emitida', $this->factura->fresh()->estado);
    }

    public function test_webhook_factura_inexistente_retorna_ok_sin_error(): void
    {
        $payload = $this->payload([
            'data' => ['transaction' => ['reference' => "FCO-{$this->empresa->id}-FE-9999-9999"]],
        ]);

        $response = $this->postJson(route('wompi.webhook'), $payload);

        $response->assertOk();
    }

    // ── FACTURA YA PROCESADA ───────────────────────────────────

    public function test_webhook_no_reprocesa_factura_ya_pagada(): void
    {
        $this->factura->update(['estado' => 'pagada', 'total_pagado' => 500_000]);

        $response = $this->postJson(route('wompi.webhook'), $this->payload());

        $response->assertOk();
        $response->assertJson(['ok' => true]);
        // El estado no debe cambiar (ya estaba pagada)
        $this->assertEquals('pagada', $this->factura->fresh()->estado);
    }

    // ── FIRMA HMAC ─────────────────────────────────────────────

    public function test_webhook_firma_invalida_retorna_401_si_hay_events_key(): void
    {
        // Configurar events key en la empresa
        $this->empresa->update(['wompi_events_key' => 'mi-secret-key']);

        $payload = $this->payload();
        // checksum incorrecto
        $payload['signature']['checksum'] = 'checksum-invalido';

        $response = $this->postJson(route('wompi.webhook'), $payload);

        $response->assertStatus(401);
        $this->assertEquals('emitida', $this->factura->fresh()->estado);
    }

    public function test_webhook_firma_valida_procesa_correctamente(): void
    {
        $eventsKey = 'mi-secret-key';
        $this->empresa->update(['wompi_events_key' => $eventsKey]);

        $reference = "FCO-{$this->empresa->id}-{$this->factura->numero}";
        $timestamp = time();

        $transaction = [
            'id'              => 'txn_test_456',
            'status'          => 'APPROVED',
            'amount_in_cents' => 50_000_000,
            'reference'       => $reference,
        ];

        $properties = ['transaction.id', 'transaction.status', 'transaction.amount_in_cents'];
        $partes     = ['txn_test_456', 'APPROVED', '50000000'];
        $cadena     = implode('', $partes) . $eventsKey . $timestamp;
        $checksum   = hash('sha256', $cadena);

        $payload = [
            'event'     => 'transaction.updated',
            'timestamp' => $timestamp,
            'data'      => ['transaction' => $transaction],
            'signature' => [
                'properties' => $properties,
                'checksum'   => $checksum,
            ],
        ];

        $response = $this->postJson(route('wompi.webhook'), $payload);

        $response->assertOk();
        $this->assertEquals('pagada', $this->factura->fresh()->estado);
    }
}
