<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\CotizacionItem;
use App\Http\Requests\StoreCotizacionRequest;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Services\DocumentoService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CotizacionController extends Controller
{
    public function __construct(
        private DocumentoService $documentos,
        private PdfService       $pdf,
    ) {}

    public function index(Request $request)
    {
        $cotizaciones = Cotizacion::with('cliente')
            ->when($request->buscar, function ($q) use ($request) {
                $q->where('numero',          'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre', 'like', '%'.$request->buscar.'%');
            })
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = [
            'total'      => Cotizacion::count(),
            'borrador'   => Cotizacion::where('estado', 'borrador')->count(),
            'enviada'    => Cotizacion::where('estado', 'enviada')->count(),
            'aceptada'   => Cotizacion::where('estado', 'aceptada')->count(),
            'convertida' => Cotizacion::where('estado', 'convertida')->count(),
        ];

        return view('cotizaciones.index', compact('cotizaciones', 'totales'));
    }

    public function create()
    {
        $consecutivo = Cotizacion::siguienteConsecutivo();
        $empresa     = Empresa::obtener();

        return view('cotizaciones.create', compact('consecutivo', 'empresa'));
    }

    public function store(StoreCotizacionRequest $request)
    {
        $data = $request->validated();
        $data['items'] = $request->items;
        $data['user_id'] = auth()->id();

        \App\Actions\CrearCotizacionAction::execute($data);

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización creada correctamente.');
    }

    public function show(Cotizacion $cotizacion)
    {
        $cotizacion->load(['items.producto', 'cliente', 'usuario', 'factura']);

        return view('cotizaciones.show', compact('cotizacion'));
    }

    public function cambiarEstado(Request $request, Cotizacion $cotizacion)
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,aceptada,rechazada',
        ]);

        $cotizacion->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado.');
    }

    // ── CONVERTIR A FACTURA ───────────────────────────────────

    public function convertir(Cotizacion $cotizacion)
    {
        try {
            \App\Actions\ConvertirCotizacionAFacturaAction::execute($cotizacion);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('cotizaciones.show', $cotizacion)
            ->with('success', '¡Cotización convertida a factura exitosamente!');
    }

    public function pdf(Cotizacion $cotizacion)
    {
        $cotizacion->load(['items', 'usuario']);
        $empresa = Empresa::obtener();

        $qrBase64 = $this->pdf->qrBase64([
            'COTIZACIÓN: ' . $cotizacion->numero,
            'CLIENTE: '    . $cotizacion->cliente_nombre,
            'TOTAL: $'     . number_format($cotizacion->total, 0, ',', '.'),
            'VÁLIDA: '     . $cotizacion->fecha_vencimiento->format('d/m/Y'),
        ], size: 100, margin: 3);

        return $this->pdf->stream(
            'cotizaciones.pdf',
            compact('cotizacion', 'empresa', 'qrBase64'),
            'cotizacion-'.$cotizacion->numero.'.pdf',
        );
    }

    public function destroy(Cotizacion $cotizacion)
    {
        $cotizacion->update(['estado' => 'anulada']);

        return redirect()->route('cotizaciones.index')
            ->with('success', 'Cotización anulada correctamente.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionó ningún elemento.');
        }

        $count = Cotizacion::whereIn('id', $ids)->where('estado', '!=', 'anulada')->count();
        Cotizacion::whereIn('id', $ids)->where('estado', '!=', 'anulada')
            ->update(['estado' => 'anulada']);

        return redirect()->route('cotizaciones.index')
            ->with('success', "{$count} cotización(es) anulada(s) correctamente.");
    }
}
