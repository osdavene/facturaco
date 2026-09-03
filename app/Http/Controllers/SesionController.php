<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LoginLog;
use App\Models\User;

class SesionController extends Controller
{
    public function index(Request $request)
    {
        // ── Auto-poblar log para la sesión actual si el historial está vacío ──
        if (LoginLog::count() === 0 && auth()->check()) {
            $u = auth()->user();
            $ua = $request->userAgent() ?? '';
            [$nav, $disp] = LoginLog::parsearUserAgent($ua);
            LoginLog::create([
                'user_id'     => $u->id,
                'ip_address'  => $request->ip(),
                'user_agent'  => $ua,
                'navegador'   => $nav,
                'dispositivo' => $disp,
                'accion'      => 'login',
                'fecha_hora'  => now('America/Bogota'),
            ]);
        }

        // ── Sesiones activas ──────────────────────────────────
        $sesiones = DB::table('sessions')
            ->join('users', 'sessions.user_id', '=', 'users.id')
            ->select(
                'sessions.id',
                'sessions.ip_address',
                'sessions.user_agent',
                'sessions.last_activity',
                'sessions.user_id',
                'users.name',
                'users.email',
                'users.cargo',
            )
            ->whereNotNull('sessions.user_id')
            ->orderByDesc('sessions.last_activity')
            ->get()
            ->map(function ($s) {
                $s->es_yo       = $s->user_id == auth()->id();
                $s->last_dt     = \Carbon\Carbon::createFromTimestamp($s->last_activity)->timezone('America/Bogota');
                $s->activo_hace = $s->last_dt->diffForHumans();
                $s->en_linea    = $s->last_dt->gt(now('America/Bogota')->subMinutes(5));
                [$nav, $disp]   = LoginLog::parsearUserAgent($s->user_agent ?? '');
                $s->navegador   = $nav;
                $s->dispositivo = $disp;
                return $s;
            });

        $totalEnLinea = $sesiones->where('en_linea', true)->count();

        // ── Historial con filtros ─────────────────────────────
        $query = LoginLog::with('user')->orderByDesc('fecha_hora');

        if ($request->filled('usuario')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->usuario . '%')
                  ->orWhere('email', 'ilike', '%' . $request->usuario . '%');
            });
        }

        if ($request->filled('accion')) {
            $query->where('accion', $request->accion);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $request->fecha_hasta);
        }

        $historial = $query->paginate(25)->withQueryString();

        $usuarios = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('sesiones.index', compact(
            'sesiones', 'totalEnLinea', 'historial', 'usuarios'
        ));
    }

    public function destroy(Request $request, string $id)
    {
        $sesion = DB::table('sessions')->where('id', $id)->first();

        if ($sesion && $sesion->user_id == auth()->id()) {
            return back()->with('error', 'No puedes cerrar tu propia sesión desde aquí.');
        }

        DB::table('sessions')->where('id', $id)->delete();

        return back()->with('success', 'Sesión cerrada correctamente.');
    }

    public function destroyAll(Request $request)
    {
        DB::table('sessions')
            ->where('user_id', '!=', auth()->id())
            ->whereNotNull('user_id')
            ->delete();

        return back()->with('success', 'Todas las demás sesiones fueron cerradas.');
    }
}