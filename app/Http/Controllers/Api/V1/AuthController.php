<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmpresaResource;
use App\Models\Empresa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/auth/login
     * Devuelve lista de empresas del usuario para que elija.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        if (! $user->activo) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        $empresas = $user->empresas()
            ->wherePivot('activo', true)
            ->get();

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'empresas'          => EmpresaResource::collection($empresas),
            'message'           => 'Selecciona una empresa y llama a /auth/tokens para obtener tu token.',
        ]);
    }

    /**
     * POST /api/v1/auth/tokens
     * Crea un token para la empresa seleccionada.
     * Requiere autenticación Basic (email:password) o token previo.
     */
    public function crearToken(Request $request): JsonResponse
    {
        $request->validate([
            'email'      => 'required|email',
            'password'   => 'required|string',
            'empresa_id' => 'required|integer|exists:empresa,id',
            'nombre'     => 'nullable|string|max:100',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciales incorrectas.'],
            ]);
        }

        if (! $user->activo) {
            return response()->json(['message' => 'Usuario inactivo.'], 403);
        }

        $empresa = Empresa::findOrFail($request->empresa_id);

        $pertenece = $user->empresas()
            ->wherePivot('activo', true)
            ->where('empresa_id', $empresa->id)
            ->exists();

        if (! $pertenece && ! $user->esSuperadmin()) {
            return response()->json(['message' => 'No tienes acceso a esta empresa.'], 403);
        }

        $nombre = $request->nombre ?? "API {$empresa->razon_social} " . now()->format('Y-m-d H:i');

        $token = $user->createToken($nombre);
        // Asigna empresa_id al token recién creado
        $token->accessToken->forceFill(['empresa_id' => $empresa->id])->save();

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_id'   => $token->accessToken->id,
            'nombre'     => $nombre,
            'empresa'    => new EmpresaResource($empresa),
            'expires_at' => null,
        ], 201);
    }

    /**
     * GET /api/v1/auth/tokens
     * Lista los tokens activos del usuario autenticado.
     */
    public function listarTokens(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()
            ->select('id', 'empresa_id', 'name', 'last_used_at', 'expires_at', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($t) => [
                'id'          => $t->id,
                'nombre'      => $t->name,
                'empresa_id'  => $t->empresa_id,
                'last_used_at' => $t->last_used_at?->toISOString(),
                'expires_at'  => $t->expires_at?->toISOString(),
                'created_at'  => $t->created_at?->toISOString(),
            ]);

        return response()->json(['data' => $tokens]);
    }

    /**
     * DELETE /api/v1/auth/tokens/{id}
     * Revoca un token específico.
     */
    public function revocarToken(Request $request, int $id): JsonResponse
    {
        $deleted = $request->user()->tokens()->where('id', $id)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Token no encontrado.'], 404);
        }

        return response()->json(['message' => 'Token revocado.']);
    }

    /**
     * DELETE /api/v1/auth/logout
     * Revoca el token actual.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
