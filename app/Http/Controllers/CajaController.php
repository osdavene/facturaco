<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use App\Models\CajaTurno;
use App\Models\Empresa;
use App\Models\MovimientoCaja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    /**
     * Obtiene o crea la caja principal de la empresa activa.
     */
    private function obtenerCajaPrincipal(): Caja
    {
        $empresa = Empresa::obtener();
        return Caja::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Caja Principal'],
            ['codigo' => 'CAJA-01', 'activa' => true]
        );
    }

    /**
     * Lista de turnos y arqueos de caja.
     */
    public function index(Request $request)
    {
        $caja = $this->obtenerCajaPrincipal();
        $query = CajaTurno::with(['usuario', 'caja'])
            ->orderByDesc('id');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_apertura', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_apertura', '<=', $request->fecha_hasta);
        }

        $turnos = $query->paginate(15)->withQueryString();
        $turnoActivo = CajaTurno::where('estado', 'abierto')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->first();

        if ($turnoActivo) {
            $turnoActivo->recalcularTotales();
        }

        return view('cajas.index', compact('turnos', 'turnoActivo', 'caja'));
    }

    /**
     * Retorna el estado actual del turno abierto para el usuario en formato JSON (usado por el POS).
     */
    public function estado()
    {
        $turnoActivo = CajaTurno::with(['movimientos.usuario'])
            ->where('estado', 'abierto')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->first();

        if ($turnoActivo) {
            $turnoActivo->recalcularTotales();
        }

        return response()->json([
            'tiene_turno_abierto' => (bool) $turnoActivo,
            'turno'               => $turnoActivo,
        ]);
    }

    /**
     * Abrir un nuevo turno de caja con base inicial en efectivo.
     */
    public function abrir(Request $request)
    {
        $request->validate([
            'monto_apertura' => 'required|numeric|min:0',
            'observaciones'  => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $caja   = $this->obtenerCajaPrincipal();

        // Verificar si ya tiene un turno abierto
        $turnoExistente = CajaTurno::where('caja_id', $caja->id)
            ->where('user_id', $userId)
            ->where('estado', 'abierto')
            ->first();

        if ($turnoExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes un turno de caja abierto.',
                'turno'   => $turnoExistente,
            ], 422);
        }

        $empresa = Empresa::obtener();
        $turno = CajaTurno::create([
            'empresa_id'             => $empresa->id,
            'caja_id'                => $caja->id,
            'user_id'                => $userId,
            'monto_apertura'         => (float) $request->monto_apertura,
            'fecha_apertura'         => now('America/Bogota'),
            'monto_cierre_esperado'  => (float) $request->monto_apertura,
            'monto_cierre_real'      => null,
            'diferencia'             => 0,
            'estado'                 => 'abierto',
            'observaciones'          => $request->observaciones,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Turno de caja abierto correctamente con base de $' . number_format($request->monto_apertura, 0, ',', '.'),
            'turno'   => $turno,
        ]);
    }

    /**
     * Registrar un movimiento menor de caja (entrada o salida de dinero).
     */
    public function movimiento(Request $request)
    {
        $request->validate([
            'tipo'   => 'required|in:entrada,salida',
            'monto'  => 'required|numeric|min:1',
            'motivo' => 'required|string|max:255',
        ]);

        $turnoActivo = CajaTurno::where('estado', 'abierto')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->first();

        if (!$turnoActivo) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un turno de caja abierto.',
            ], 422);
        }

        $empresa = Empresa::obtener();
        $movimiento = MovimientoCaja::create([
            'empresa_id'    => $empresa->id,
            'caja_turno_id' => $turnoActivo->id,
            'tipo'          => $request->tipo,
            'monto'         => (float) $request->monto,
            'motivo'        => $request->motivo,
            'user_id'       => Auth::id(),
        ]);

        $turnoActivo->recalcularTotales();

        return response()->json([
            'success'    => true,
            'message'    => ucfirst($request->tipo) . ' de caja registrada con éxito.',
            'movimiento' => $movimiento,
            'turno'      => $turnoActivo->fresh(),
        ]);
    }

    /**
     * Cierre de Turno / Arqueo de caja.
     */
    public function cerrar(Request $request)
    {
        $request->validate([
            'monto_cierre_real' => 'required|numeric|min:0',
            'observaciones'     => 'nullable|string|max:500',
        ]);

        $turnoActivo = CajaTurno::where('estado', 'abierto')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->first();

        if (!$turnoActivo) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un turno de caja abierto para cerrar.',
            ], 422);
        }

        $turnoActivo->recalcularTotales();
        $montoReal     = (float) $request->monto_cierre_real;
        $montoEsperado = (float) $turnoActivo->monto_cierre_esperado;
        $diferencia    = round($montoReal - $montoEsperado, 2);

        $turnoActivo->update([
            'monto_cierre_real' => $montoReal,
            'diferencia'        => $diferencia,
            'estado'            => 'cerrado',
            'fecha_cierre'      => now('America/Bogota'),
            'observaciones'     => $request->observaciones ?: $turnoActivo->observaciones,
        ]);

        return response()->json([
            'success'    => true,
            'message'    => 'Turno de caja cerrado exitosamente.',
            'ticket_url' => route('cajas.cierre_ticket', $turnoActivo->id),
            'turno'      => $turnoActivo->fresh(),
        ]);
    }

    /**
     * Reporte Z / Tirilla de Cierre de Caja imprimible en 80mm / 58mm.
     */
    public function cierreTicket(CajaTurno $turno)
    {
        $turno->load(['usuario', 'caja', 'movimientos.usuario']);
        $turno->recalcularTotales();
        $empresa = Empresa::obtener();
        $ancho = request('ancho', '80');

        return view('cajas.cierre_ticket', compact('turno', 'empresa', 'ancho'));
    }
}
