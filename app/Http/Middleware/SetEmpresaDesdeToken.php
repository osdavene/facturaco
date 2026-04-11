<?php

namespace App\Http\Middleware;

use App\Models\Empresa;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve la empresa activa a partir del empresa_id almacenado en el token.
 * Debe ejecutarse DESPUÉS del guard auth:sanctum.
 */
class SetEmpresaDesdeToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()?->currentAccessToken();

        if (! $token) {
            return response()->json(['message' => 'Token inválido.'], 401);
        }

        $empresaId = $token->empresa_id;

        if (! $empresaId) {
            return response()->json(['message' => 'El token no tiene empresa asociada.'], 403);
        }

        $empresa = Empresa::find($empresaId);

        if (! $empresa) {
            return response()->json(['message' => 'Empresa no encontrada.'], 404);
        }

        // Verifica que el usuario pertenezca a esta empresa
        $pertenece = $request->user()
            ->empresas()
            ->wherePivot('activo', true)
            ->where('empresa_id', $empresaId)
            ->exists();

        if (! $pertenece && ! $request->user()->esSuperadmin()) {
            return response()->json(['message' => 'No tienes acceso a esta empresa.'], 403);
        }

        // Inyecta empresa en el request para los controladores
        $request->merge(['_empresa' => $empresa]);
        $request->attributes->set('empresa', $empresa);

        // Los global scopes de PertenecerEmpresa/PertenecerGrupo leen de session —
        // forzamos los valores necesarios para que funcionen en contexto API.
        session([
            'empresa_activa_id' => $empresa->id,
            'empresa_raiz_id'   => $empresa->raiz()->id,
            'empresa_grupo_ids' => $empresa->idsGrupo(),
        ]);

        return $next($request);
    }
}
