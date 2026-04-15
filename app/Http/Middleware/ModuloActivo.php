<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuloActivo
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $slug): Response
    {
        $empresaId = session('empresa_activa_id');

        if (! $empresaId) {
            return redirect()->route('empresas.seleccionar')
                ->with('error', 'Debes seleccionar una empresa.');
        }

        $empresa = \App\Models\Empresa::find($empresaId);

        if (! $empresa || ! $empresa->tieneModulo($slug)) {
            abort(403, 'Este módulo no está habilitado para tu empresa.');
        }

        return $next($request);
    }
}
