<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Nomina;
use App\Models\NominaEmpleado;
use App\Services\NominaService;
use Illuminate\Http\Request;

class NominaController extends Controller
{
    public function __construct(private NominaService $servicio) {}

    // ── Períodos de nómina ────────────────────────────────────────

    public function index(Request $request)
    {
        $nominas = Nomina::orderByDesc('periodo_inicio')
            ->paginate(15)
            ->withQueryString();

        return view('nomina.index', compact('nominas'));
    }

    public function create()
    {
        $empleados = Empleado::activos()->orderBy('apellidos')->get();

        // Sugerir período actual
        $hoy = now();
        $periodoInicio = $hoy->copy()->startOfMonth()->format('Y-m-d');
        $periodoFin    = $hoy->copy()->endOfMonth()->format('Y-m-d');
        $nombreSug     = 'Nómina ' . $hoy->locale('es')->isoFormat('MMMM YYYY');

        return view('nomina.create', compact('empleados', 'periodoInicio', 'periodoFin', 'nombreSug'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'         => 'required|string|max:150',
            'periodo_inicio' => 'required|date',
            'periodo_fin'    => 'required|date|after_or_equal:periodo_inicio',
            'periodicidad'   => 'required|in:mensual,quincenal',
            'fecha_pago'     => 'nullable|date',
            'observaciones'  => 'nullable|string|max:500',
            'empleados'      => 'required|array|min:1',
            'empleados.*'    => 'exists:empleados,id',
        ]);

        $nomina = Nomina::create([
            'nombre'         => $request->nombre,
            'periodo_inicio' => $request->periodo_inicio,
            'periodo_fin'    => $request->periodo_fin,
            'fecha_pago'     => $request->fecha_pago,
            'periodicidad'   => $request->periodicidad,
            'observaciones'  => $request->observaciones,
            'created_by'     => auth()->id(),
            'estado'         => 'borrador',
        ]);

        // Crear liquidaciones iniciales para cada empleado seleccionado
        foreach ($request->empleados as $empId) {
            $empleado = Empleado::findOrFail($empId);

            // Calcular días según periodicidad
            $dias = $request->periodicidad === 'quincenal' ? 15 : 30;

            $data = $this->servicio->calcularLiquidacion($empleado, ['dias_trabajados' => $dias]);

            NominaEmpleado::create(array_merge($data, [
                'nomina_id'   => $nomina->id,
                'empleado_id' => $empId,
            ]));
        }

        $this->servicio->recalcularTotalesNomina($nomina);

        return redirect()->route('nomina.show', $nomina)
            ->with('success', 'Nómina creada. Revisa y ajusta los valores antes de procesar.');
    }

    public function show(Nomina $nomina)
    {
        $nomina->load('liquidaciones.empleado');

        return view('nomina.show', compact('nomina'));
    }

    // ── Actualizar liquidación individual ─────────────────────────

    public function actualizarLiquidacion(Request $request, Nomina $nomina, NominaEmpleado $liquidacion)
    {
        abort_if($liquidacion->nomina_id !== $nomina->id, 404);
        abort_if($nomina->estado === 'pagada', 403, 'La nómina ya fue pagada.');

        $data = $this->servicio->calcularLiquidacion(
            $liquidacion->empleado,
            $request->only([
                'dias_trabajados', 'dias_vacaciones', 'dias_incapacidad', 'dias_licencia_remunerada',
                'horas_extras_diurnas', 'horas_extras_nocturnas',
                'horas_extras_fest_diurnas', 'horas_extras_fest_nocturnas',
                'horas_recargo_nocturno',
                'comisiones', 'bonificaciones', 'otros_devengados',
                'retencion_fuente', 'otras_deducciones',
            ])
        );

        $liquidacion->update(array_merge($data, [
            'observaciones' => $request->observaciones,
        ]));

        $this->servicio->recalcularTotalesNomina($nomina);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'liquidacion' => $liquidacion->fresh()]);
        }

        return back()->with('success', 'Liquidación actualizada.');
    }

    // ── Cambiar estado ────────────────────────────────────────────

    public function procesar(Nomina $nomina)
    {
        abort_if($nomina->estado !== 'borrador', 403);

        $nomina->update(['estado' => 'procesada']);

        return back()->with('success', 'Nómina procesada correctamente.');
    }

    public function marcarPagada(Nomina $nomina)
    {
        abort_if($nomina->estado !== 'procesada', 403);

        $nomina->update([
            'estado'      => 'pagada',
            'fecha_pago'  => $nomina->fecha_pago ?? now()->toDateString(),
        ]);

        return back()->with('success', 'Nómina marcada como pagada.');
    }

    public function anular(Nomina $nomina)
    {
        abort_if($nomina->estado === 'pagada', 403, 'No se puede anular una nómina pagada.');

        $nomina->update(['estado' => 'anulada']);

        return back()->with('success', 'Nómina anulada.');
    }

    // ── Colilla de pago ───────────────────────────────────────────

    public function colilla(Nomina $nomina, NominaEmpleado $liquidacion)
    {
        abort_if($liquidacion->nomina_id !== $nomina->id, 404);
        $liquidacion->load('empleado');

        $empresa = \App\Models\Empresa::obtener();

        return view('nomina.colilla', compact('nomina', 'liquidacion', 'empresa'));
    }

    public function destroy(Nomina $nomina)
    {
        abort_if($nomina->estado === 'pagada', 403, 'No se puede eliminar una nómina pagada.');

        $nomina->delete();

        return redirect()->route('nomina.index')
            ->with('success', 'Nómina eliminada.');
    }
}
