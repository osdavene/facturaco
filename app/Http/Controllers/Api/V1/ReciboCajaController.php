<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReciboCajaResource;
use App\Models\ReciboCaja;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReciboCajaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $recibos = ReciboCaja::with('cliente')
            ->when($request->buscar, fn ($q) => $q
                ->where('numero', 'ilike', "%{$request->buscar}%")
                ->orWhere('cliente_nombre', 'ilike', "%{$request->buscar}%"))
            ->when($request->fecha_desde, fn ($q) => $q->whereDate('fecha', '>=', $request->fecha_desde))
            ->when($request->fecha_hasta, fn ($q) => $q->whereDate('fecha', '<=', $request->fecha_hasta))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json(ReciboCajaResource::collection($recibos)->response()->getData(true));
    }

    public function show(ReciboCaja $reciboCaja): JsonResponse
    {
        return response()->json(['data' => new ReciboCajaResource($reciboCaja)]);
    }

    public function destroy(ReciboCaja $reciboCaja): JsonResponse
    {
        $reciboCaja->delete();

        return response()->json(['message' => 'Recibo de caja eliminado.']);
    }
}
