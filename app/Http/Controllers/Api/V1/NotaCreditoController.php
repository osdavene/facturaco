<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotaCreditoResource;
use App\Models\NotaCredito;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotaCreditoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notas = NotaCredito::with('cliente')
            ->when($request->buscar, fn ($q) => $q
                ->where('numero', 'ilike', "%{$request->buscar}%")
                ->orWhere('cliente_nombre', 'ilike', "%{$request->buscar}%"))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json(NotaCreditoResource::collection($notas)->response()->getData(true));
    }

    public function show(NotaCredito $notaCredito): JsonResponse
    {
        return response()->json(['data' => new NotaCreditoResource($notaCredito->load('items'))]);
    }

    public function destroy(NotaCredito $notaCredito): JsonResponse
    {
        $notaCredito->update(['estado' => 'anulada']);

        return response()->json(['message' => 'Nota crédito anulada correctamente.']);
    }
}
