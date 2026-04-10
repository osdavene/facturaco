<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WompiController extends Controller
{
    /**
     * Webhook POST recibido desde los servidores de Wompi.
     * Debe registrarse como URL de eventos en comercios.wompi.co
     * URL: POST /webhooks/wompi  (excluida de CSRF)
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();

        // Solo procesar transacciones
        if (($payload['event'] ?? '') !== 'transaction.updated') {
            return response()->json(['ok' => true]);
        }

        $empresa = Empresa::obtener();

        // Verificar firma HMAC-SHA256 si hay events key configurada
        if ($empresa->wompi_events_key) {
            $sig = $payload['signature'] ?? [];

            if (!isset($sig['properties'], $sig['checksum'], $payload['timestamp'])) {
                Log::warning('Wompi webhook: firma incompleta', $payload);
                return response()->json(['error' => 'firma incompleta'], 400);
            }

            $transaction = $payload['data']['transaction'] ?? [];
            $partes      = [];

            foreach ($sig['properties'] as $prop) {
                // Convertir "transaction.id" → acceder a $transaction['id']
                $key      = str_replace('transaction.', '', $prop);
                $partes[] = $transaction[$key] ?? '';
            }

            $cadena   = implode('', $partes) . $empresa->wompi_events_key . $payload['timestamp'];
            $esperado = hash('sha256', $cadena);

            if (!hash_equals($esperado, $sig['checksum'])) {
                Log::warning('Wompi webhook: firma inválida');
                return response()->json(['error' => 'firma inválida'], 401);
            }
        }

        $transaction = $payload['data']['transaction'] ?? [];
        $status      = $transaction['status'] ?? '';
        $reference   = $transaction['reference'] ?? '';
        $amountCents = intval($transaction['amount_in_cents'] ?? 0);

        if ($status !== 'APPROVED') {
            return response()->json(['ok' => true, 'ignorado' => 'no aprobado']);
        }

        // La referencia tiene formato "FCO-{empresa_id}-{factura_numero}"
        // Ejemplo: "FCO-1-FE-2026-00001"
        if (!preg_match('/^FCO-(\d+)-(.+)$/', $reference, $m)) {
            Log::warning("Wompi webhook: referencia malformada: {$reference}");
            return response()->json(['ok' => true, 'ignorado' => 'referencia no reconocida']);
        }

        $empresaId     = (int) $m[1];
        $facturaNumero = $m[2];

        // Buscar sin el global scope de empresa (contexto de webhook)
        $factura = Factura::sinFiltroEmpresa()
                          ->where('empresa_id', $empresaId)
                          ->where('numero', $facturaNumero)
                          ->first();

        if (!$factura) {
            Log::warning("Wompi webhook: factura no encontrada para referencia {$reference}");
            return response()->json(['ok' => true, 'ignorado' => 'factura no encontrada']);
        }

        if (in_array($factura->estado, ['pagada', 'anulada'])) {
            return response()->json(['ok' => true, 'ignorado' => 'factura ya procesada']);
        }

        DB::transaction(function () use ($factura, $amountCents) {
            $montoPagado = $amountCents / 100;
            $nuevoPagado = min($factura->total, $factura->total_pagado + $montoPagado);

            $factura->update([
                'total_pagado' => $nuevoPagado,
                'estado'       => $nuevoPagado >= $factura->total ? 'pagada' : $factura->estado,
            ]);
        });

        Log::info("Wompi: pago aprobado factura {$factura->numero} por $" . ($amountCents / 100));

        return response()->json(['ok' => true]);
    }

    /**
     * Retorno del cliente desde el checkout de Wompi.
     * Wompi redirige aquí con ?id=xxx&status=APPROVED|DECLINED&reference=FCO-xxx
     */
    public function retorno(Factura $factura, Request $request)
    {
        $status = $request->query('status', '');

        if ($status === 'APPROVED') {
            // Recargar desde DB para obtener estado actualizado por webhook
            $factura->refresh();
            return redirect()
                ->route('facturas.show', $factura)
                ->with('success', '¡Pago recibido con éxito! La factura ha sido actualizada.');
        }

        if (in_array($status, ['DECLINED', 'ERROR', 'VOIDED'])) {
            return redirect()
                ->route('facturas.show', $factura)
                ->with('error', 'El pago no pudo procesarse. Intenta nuevamente o elige otro método de pago.');
        }

        // PENDING u otro estado
        return redirect()
            ->route('facturas.show', $factura)
            ->with('info', 'Tu pago está siendo procesado. La factura se actualizará automáticamente cuando se confirme.');
    }
}
