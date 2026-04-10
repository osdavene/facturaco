<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SesionController extends Controller
{
    public function index()
    {
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
                $s->last_dt     = \Carbon\Carbon::createFromTimestamp($s->last_activity);
                $s->activo_hace = $s->last_dt->diffForHumans();
                $s->en_linea    = $s->last_dt->gt(now()->subMinutes(5));
                $s->navegador   = $this->parsearNavegador($s->user_agent ?? '');
                $s->dispositivo = $this->parsearDispositivo($s->user_agent ?? '');
                return $s;
            });

        $totalEnLinea = $sesiones->where('en_linea', true)->count();

        return view('sesiones.index', compact('sesiones', 'totalEnLinea'));
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

    private function parsearNavegador(string $ua): string
    {
        if (str_contains($ua, 'Edg'))     return 'Edge';
        if (str_contains($ua, 'OPR'))     return 'Opera';
        if (str_contains($ua, 'Chrome'))  return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari'))  return 'Safari';
        if (str_contains($ua, 'MSIE') || str_contains($ua, 'Trident')) return 'Internet Explorer';
        return 'Desconocido';
    }

    private function parsearDispositivo(string $ua): string
    {
        if (str_contains($ua, 'iPhone') || (str_contains($ua, 'Android') && str_contains($ua, 'Mobile'))) return 'Móvil';
        if (str_contains($ua, 'iPad')   || str_contains($ua, 'Android')) return 'Tablet';
        return 'Escritorio';
    }
}