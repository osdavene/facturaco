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

        // Generar asiento contable automático en Libro Diario
        try {
            (new \App\Services\ContabilidadService())->asientoNomina($nomina);
        } catch (\Throwable $e) {}

        return back()->with('success', 'Nómina marcada como pagada y asiento contable generado en el Libro Diario.');
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

    public function enviarColilla(Nomina $nomina, NominaEmpleado $liquidacion, \App\Services\MailService $mailer)
    {
        abort_if($liquidacion->nomina_id !== $nomina->id, 404);
        $liquidacion->load('empleado');
        $empresa = \App\Models\Empresa::obtener();

        if (empty($liquidacion->empleado?->email)) {
            return back()->with('error', "El empleado {$liquidacion->empleado?->nombre_completo} no tiene correo electrónico.");
        }

        try {
            $mailer->enviarColillaPago($nomina, $liquidacion, $empresa);
            return back()->with('success', "Desprendible de pago enviado a {$liquidacion->empleado->email}.");
        } catch (\Throwable $e) {
            return back()->with('error', "Error enviando correo: " . $e->getMessage());
        }
    }

    public function enviarColillasTodas(Nomina $nomina, \App\Services\MailService $mailer)
    {
        $nomina->load('liquidaciones.empleado');
        $empresa = \App\Models\Empresa::obtener();
        $enviados = 0;
        $fallidos = 0;

        foreach ($nomina->liquidaciones as $liq) {
            if (!empty($liq->empleado?->email)) {
                try {
                    $mailer->enviarColillaPago($nomina, $liq, $empresa);
                    $enviados++;
                } catch (\Throwable) {
                    $fallidos++;
                }
            } else {
                $fallidos++;
            }
        }

        return back()->with('success', "Envío masivo completado: {$enviados} desprendibles enviados correctamente" . ($fallidos > 0 ? " ({$fallidos} sin correo o fallaron)." : "."));
    }

    public function exportarBanco(Nomina $nomina)
    {
        $nomina->load('liquidaciones.empleado');
        $empresa = \App\Models\Empresa::obtener();

        $filename = 'Dispersion_Nomina_' . \Illuminate\Support\Str::slug($nomina->nombre) . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($nomina, $empresa) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['EMPRESA:', $empresa->razon_social, 'NIT:', $empresa->nit . '-' . $empresa->digito_verificacion], ';');
            fputcsv($file, ['PERIODO:', $nomina->nombre, 'FECHA PAGO:', $nomina->fecha_pago ? $nomina->fecha_pago->format('Y-m-d') : date('Y-m-d')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['Tipo Doc', 'Número Documento', 'Nombre del Empleado', 'Banco', 'Tipo Cuenta', 'Número de Cuenta', 'Valor Neto a Pagar (COP)'], ';');

            foreach ($nomina->liquidaciones as $l) {
                $emp = $l->empleado;
                fputcsv($file, [
                    $emp->tipo_documento ?? 'CC',
                    $emp->numero_documento ?? '',
                    $emp->nombre_completo ?? '',
                    $emp->banco ?? 'No registrado',
                    ucfirst($emp->tipo_cuenta ?? 'Ahorros'),
                    $emp->numero_cuenta ?? 'Sin cuenta',
                    number_format($l->neto_pagar, 2, ',', ''),
                ], ';');
            }

            fputcsv($file, [
                'TOTAL A DISPERSAR', '', '', '', '', '',
                number_format($nomina->total_neto, 2, ',', ''),
            ], ';');

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function destroy(Nomina $nomina)
    {
        abort_if($nomina->estado === 'pagada', 403, 'No se puede anular una nómina pagada.');

        $nomina->update(['estado' => 'anulada']);

        return redirect()->route('nomina.index')
            ->with('success', 'Nómina anulada correctamente.');
    }
}
