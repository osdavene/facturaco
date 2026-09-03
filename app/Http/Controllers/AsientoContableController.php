<?php

namespace App\Http\Controllers;

use App\Models\AsientoContable;
use App\Models\PlanCuenta;
use App\Services\ContabilidadService;
use Illuminate\Http\Request;

class AsientoContableController extends Controller
{
    public function __construct(private ContabilidadService $contabilidad) {}

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

    public function create()
    {
        $empresaId = session('empresa_activa_id');
        $cuentas = PlanCuenta::deEmpresa($empresaId)
            ->activas()
            ->conMovimientos()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'tipo', 'naturaleza']);

        return view('contabilidad.libro-diario.create', compact('cuentas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha'               => 'required|date',
            'descripcion'         => 'required|string|max:500',
            'lineas'              => 'required|array|min:2',
            'lineas.*.cuenta_id'  => 'required|exists:plan_cuentas,id',
            'lineas.*.descripcion'=> 'nullable|string|max:300',
            'lineas.*.debito'     => 'nullable|numeric|min:0',
            'lineas.*.credito'    => 'nullable|numeric|min:0',
        ]);

        $empresaId = session('empresa_activa_id');

        // Limpiar líneas y formatear montos
        $lineasValidas = [];
        foreach ($request->lineas as $l) {
            $deb = (float) ($l['debito'] ?? 0);
            $cred = (float) ($l['credito'] ?? 0);

            if ($deb > 0 || $cred > 0) {
                $lineasValidas[] = [
                    'cuenta_id'   => (int) $l['cuenta_id'],
                    'descripcion' => $l['descripcion'] ?? $request->descripcion,
                    'debito'      => $deb,
                    'credito'     => $cred,
                ];
            }
        }

        if (count($lineasValidas) < 2) {
            return back()->withInput()->with('error', 'El asiento debe tener al menos dos líneas con valores.');
        }

        try {
            $asiento = $this->contabilidad->crearAsientoManual(
                $empresaId,
                $request->fecha,
                $request->descripcion,
                $lineasValidas,
                auth()->id()
            );

            return redirect()->route('contabilidad.libro-diario.show', $asiento)
                ->with('success', "Asiento contable {$asiento->numero} registrado correctamente.");
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
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

    public function anular(AsientoContable $asiento)
    {
        $empresaId = session('empresa_activa_id');
        if ($asiento->empresa_id !== $empresaId) {
            abort(403);
        }

        if ($asiento->tipo !== 'manual') {
            return back()->with('error', 'Solo los asientos manuales se pueden anular desde aquí. Los automáticos se anulan al anular su documento de origen.');
        }

        $asiento->update(['estado' => 'anulado']);

        return back()->with('success', "Asiento {$asiento->numero} anulado correctamente.");
    }

    public function exportar(Request $request)
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
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $filename = 'Libro_Diario_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($asientos) {
            $file = fopen('php://output', 'w');
            // UTF-8 BOM para que Excel en Windows lo abra con tildes y caracteres correctos
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, ['Número Comprobante', 'Fecha', 'Tipo', 'Estado', 'Código Cuenta', 'Nombre Cuenta', 'Concepto / Detalle', 'Débito (COP)', 'Crédito (COP)'], ';');

            foreach ($asientos as $asiento) {
                foreach ($asiento->lineas as $l) {
                    fputcsv($file, [
                        $asiento->numero,
                        $asiento->fecha->format('Y-m-d'),
                        ucfirst($asiento->tipo),
                        ucfirst($asiento->estado),
                        $l->cuenta->codigo ?? '',
                        $l->cuenta->nombre ?? '',
                        $l->descripcion ?: $asiento->descripcion,
                        number_format($l->debito, 2, ',', ''),
                        number_format($l->credito, 2, ',', ''),
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
