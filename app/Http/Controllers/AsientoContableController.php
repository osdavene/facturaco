<?php

namespace App\Http\Controllers;

use App\Models\AsientoContable;
use Illuminate\Http\Request;

class AsientoContableController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = session('empresa_activa_id');

        $asientos = AsientoContable::with('lineas.cuenta')
            ->where('empresa_id', $empresaId)
            ->when($request->buscar, fn($q) =>
                $q->where('numero', 'like', '%' . $request->buscar . '%')
                  ->orWhere('descripcion', 'ilike', '%' . $request->buscar . '%')
            )
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->when($request->fecha_desde, fn($q) => $q->whereDate('fecha', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn($q) => $q->whereDate('fecha', '<=', $request->fecha_hasta))
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('contabilidad.libro-diario.index', compact('asientos'));
    }

    public function show(AsientoContable $asiento)
    {
        $empresaId = session('empresa_activa_id');
        if ($asiento->empresa_id !== $empresaId) {
            abort(403);
        }

        $asiento->load('lineas.cuenta', 'creadoPor');

        return view('contabilidad.libro-diario.show', compact('asiento'));
    }
}
