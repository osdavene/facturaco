<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrdenCompraRequest;
use App\Http\Requests\UpdateOrdenCompraRequest;
use App\Models\Empresa;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Services\OrdenCompraService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class OrdenCompraController extends Controller
{
    public function __construct(
        private OrdenCompraService $ordenes,
        private PdfService         $pdf,
    ) {}

    public function index(Request $request)
    {
        $ordenes = OrdenCompra::with('proveedor')
            ->when($request->buscar, fn($q) => $q->where(function ($inner) use ($request) {
                $inner->where('numero',           'ilike', '%'.$request->buscar.'%')
                      ->orWhere('proveedor_nombre', 'ilike', '%'.$request->buscar.'%');
            }))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = [
            'total'    => OrdenCompra::count(),
            'borrador' => OrdenCompra::where('estado', 'borrador')->count(),
            'aprobada' => OrdenCompra::where('estado', 'aprobada')->count(),
            'recibida' => OrdenCompra::where('estado', 'recibida')->count(),
        ];

        return view('ordenes.index', compact('ordenes', 'totales'));
    }

    public function create()
    {
        $consecutivo = OrdenCompra::siguienteConsecutivo();
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();

        return view('ordenes.create', compact('consecutivo', 'proveedores'));
    }

    public function store(StoreOrdenCompraRequest $request)
    {

        $this->ordenes->crear($request, auth()->id());

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden de compra creada correctamente.');
    }

    public function show(OrdenCompra $orden)
    {
        $orden->load(['items.producto', 'proveedor', 'usuario']);

        return view('ordenes.show', compact('orden'));
    }

    public function edit(OrdenCompra $orden)
    {
        if ($orden->estado !== 'borrador') {
            return redirect()->route('ordenes.show', $orden)
                ->with('error', 'Solo puedes editar órdenes en borrador.');
        }

        $orden->load('items');
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();

        return view('ordenes.create', compact('orden', 'proveedores'));
    }

    public function update(UpdateOrdenCompraRequest $request, OrdenCompra $orden)
    {

        $this->ordenes->actualizar($orden, $request);

        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Orden actualizada correctamente.');
    }

    public function destroy(OrdenCompra $orden)
    {
        $orden->update(['estado' => 'anulada']);

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden anulada correctamente.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionó ningún elemento.');
        }

        $count = OrdenCompra::whereIn('id', $ids)
            ->where('estado', '!=', 'anulada')
            ->update(['estado' => 'anulada']);

        return redirect()->route('ordenes.index')
            ->with('success', "{$count} orden(es) anulada(s) correctamente.");
    }

    public function cambiarEstado(Request $request, OrdenCompra $orden)
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,aprobada,recibida,anulada',
        ]);

        $orden->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function recibir(Request $request, OrdenCompra $orden)
    {
        if ($orden->estado !== 'aprobada') {
            return back()->with('error', 'Solo puedes recibir órdenes aprobadas.');
        }

        $this->ordenes->recibir($orden, $request, auth()->id());

        return redirect()->route('ordenes.show', $orden)
            ->with('success', '¡Mercancía recibida! El inventario ha sido actualizado.');
    }

    public function pdf(OrdenCompra $orden)
    {
        $orden->load(['items', 'proveedor']);
        $empresa = Empresa::obtener();

        return $this->pdf->stream(
            'ordenes.pdf',
            compact('orden', 'empresa'),
            'orden-'.$orden->numero.'.pdf',
        );
    }
}
