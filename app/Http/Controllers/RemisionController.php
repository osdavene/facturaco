<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRemisionRequest;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\Remision;
use App\Models\RemisionItem;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RemisionController extends Controller
{
    public function __construct(
        private PdfService $pdf,
    ) {}

    public function index(Request $request)
    {
        $remisiones = Remision::with('cliente')
            ->when($request->buscar, fn ($q) =>
                $q->where('numero',          'like', '%'.$request->buscar.'%')
                  ->orWhere('cliente_nombre', 'like', '%'.$request->buscar.'%'))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = [
            'total'     => Remision::count(),
            'borrador'  => Remision::where('estado', 'borrador')->count(),
            'enviada'   => Remision::where('estado', 'enviada')->count(),
            'entregada' => Remision::where('estado', 'entregada')->count(),
            'facturada' => Remision::where('estado', 'facturada')->count(),
        ];

        return view('remisiones.index', compact('remisiones', 'totales'));
    }

    public function create()
    {
        $consecutivo = Remision::siguienteConsecutivo();
        $empresa     = Empresa::obtener();

        return view('remisiones.create', compact('consecutivo', 'empresa'));
    }

    public function store(StoreRemisionRequest $request)
    {
        $data = $request->validated();
        $data['items'] = $request->items;
        $data['user_id'] = auth()->id();

        \App\Actions\CrearRemisionAction::execute($data);

        return redirect()->route('remisiones.index')
            ->with('success', 'Remisión creada correctamente.');
    }

    public function show(Remision $remision)
    {
        $remision->load(['items.producto', 'cliente', 'usuario', 'factura']);

        return view('remisiones.show', compact('remision'));
    }

    public function cambiarEstado(Request $request, Remision $remision)
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,entregada,anulada',
        ]);

        $remision->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado.');
    }

    // ── CONVERTIR A FACTURA ───────────────────────────────────

    public function convertir(Remision $remision)
    {
        try {
            \App\Actions\ConvertirRemisionAFacturaAction::execute($remision);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('remisiones.show', $remision)
            ->with('success', '¡Remisión convertida a factura exitosamente!');
    }

    public function destroy(Remision $remision)
    {
        $remision->update(['estado' => 'anulada']);

        return redirect()->route('remisiones.index')
            ->with('success', 'Remisión anulada correctamente.');
    }

    public function pdf(Remision $remision)
    {
        $remision->load(['items', 'usuario']);
        $empresa = Empresa::obtener();

        return $this->pdf->stream(
            'remisiones.pdf',
            compact('remision', 'empresa'),
            'remision-'.$remision->numero.'.pdf',
        );
    }
}
