<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmpresaSeleccionada
{
    /** Rutas que este middleware no intercepta (para evitar loops) */
    private array $except = [
        'empresas.seleccionar',
        'empresas.elegir',
        'empresas.crear',
        'empresas.store',
        'logout',
        'perfil.*',
        'profile.*',
        'tema.cambiar',
        'wompi.webhook',
    ];

    public function handle(Request $request, Closure $next): mixed
    {
        if (!auth()->check()) {
            return $next($request);
        }

        // Dejar pasar las rutas exceptuadas
        foreach ($this->except as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        $empresaActivaId = session('empresa_activa_id');

        // Verificar que la empresa en sesión sigue siendo válida para el usuario
        if ($empresaActivaId) {
            $sigue = auth()->user()
                ->empresas()
                ->where('empresa_id', $empresaActivaId)
                ->where('activo', true)
                ->exists();

            if (!$sigue) {
                session()->forget('empresa_activa_id');
                $empresaActivaId = null;
            }
        }

        if (!$empresaActivaId) {
            // Cargar empresas activas del usuario
            $empresas = auth()->user()
                ->empresas()
                ->wherePivot('activo', true)
                ->get();

            if ($empresas->isEmpty()) {
                // Sin empresa: crear una
                return redirect()->route('empresas.crear');
            }

            if ($empresas->count() === 1) {
                // Solo una: auto-seleccionar
                session(['empresa_activa_id' => $empresas->first()->id]);
                return $next($request);
            }

            // Varias: mostrar selector
            return redirect()->route('empresas.seleccionar');
        }

        return $next($request);
    }
}
