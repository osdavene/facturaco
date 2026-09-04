<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\Factura;
use App\Services\PdfService;
use Illuminate\Http\Request;

class PagoPublicoController extends Controller
{
    public function __construct(
        private PdfService $pdf
    ) {}

    /**
     * Muestra la pantalla pública de pago para el cliente final.
     */
    public function mostrar(string $token)
    {
        $factura = Factura::sinFiltroEmpresa()
            ->with(['items', 'cliente', 'empresa'])
            ->where('token_pago', $token)
            ->firstOrFail();

        $empresa = $factura->empresa ?? Empresa::find($factura->empresa_id);

        // Generar referencia única para Wompi si está configurado
        // Formato: FCO-{empresa_id}-{factura_numero}
        $wompiReferencia = "FCO-{$factura->empresa_id}-{$factura->numero}";
        $montoEnCentavos = intval(round($factura->saldo_pendiente * 100));

        // Firma de integridad de Wompi (si la empresa configuró integrity secret)
        $wompiSignature = null;
        if (!empty($empresa->wompi_integrity_key) && $montoEnCentavos > 0) {
            $cadena = "{$wompiReferencia}{$montoEnCentavos}COP{$empresa->wompi_integrity_key}";
            $wompiSignature = hash('sha256', $cadena);
        }

        return view('facturas.publica', compact('factura', 'empresa', 'wompiReferencia', 'montoEnCentavos', 'wompiSignature'));
    }

    /**
     * Genera la URL de compartir por WhatsApp con mensaje pre-redactado.
     */
    public function whatsapp(Factura $factura)
    {
        $factura->load(['cliente', 'empresa']);
        $empresa = $factura->empresa ?? Empresa::obtener();
        $telefono = preg_replace('/\D/', '', $factura->cliente->celular ?? $factura->cliente->telefono ?? '');

        $urlPago = $factura->url_pago;
        $totalFormateado = '$' . number_format($factura->total, 0, ',', '.');
        $saldoFormateado = '$' . number_format($factura->saldo_pendiente, 0, ',', '.');

        $empresaNombre = $empresa->nombre_comercial ?: $empresa->razon_social;
        $clienteNombre = $factura->cliente_nombre;

        $mensaje = "Hola *{$clienteNombre}*, le compartimos su *Factura {$factura->numero}* emitida por *{$empresaNombre}*.\n\n"
                 . "📄 *Total:* {$totalFormateado}\n"
                 . "💰 *Saldo Pendiente:* {$saldoFormateado}\n"
                 . "📅 *Vencimiento:* " . ($factura->fecha_vencimiento ? $factura->fecha_vencimiento->format('d/m/Y') : 'Inmediato') . "\n\n"
                 . "💳 Puede consultar su detalle y pagar en línea de forma rápida y segura aquí:\n"
                 . "👉 {$urlPago}\n\n"
                 . "¡Gracias por su preferencia!";

        $url = "https://api.whatsapp.com/send?phone=" . ($telefono ? (str_starts_with($telefono, '57') ? $telefono : '57' . $telefono) : '')
             . "&text=" . urlencode($mensaje);

        return redirect()->away($url);
    }
}
