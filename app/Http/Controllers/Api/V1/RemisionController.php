<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RemisionResource;
use App\Models\Remision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RemisionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $remisiones = Remision::with('cliente')
            ->when($request->buscar, fn ($q) => $q
                ->where('numero', 'ilike', "%{$request->buscar}%")
                ->orWhere('cliente_nombre', 'ilike', "%{$request->buscar}%"))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json(RemisionResource::collection($remisiones)->response()->getData(true));
    }

    public function show(Remision $remision): JsonResponse
    {
        return response()->json(['data' => new RemisionResource($remision->load('items'))]);
    }

    public function estado(Request $request, Remision $remision): JsonResponse
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,entregada,anulada',
        ]);

        $remision->update(['estado' => $request->estado]);

        return response()->json(['data' => new RemisionResource($remision->fresh())]);
    }

    public function destroy(Remision $remision): JsonResponse
    {
        $remision->update(['estado' => 'anulada']);

        return response()->json(['message' => 'Remisión anulada correctamente.']);
    }
}
