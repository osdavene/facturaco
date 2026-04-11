<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrdenCompraResource;
use App\Models\OrdenCompra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrdenCompraController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $ordenes = OrdenCompra::with('proveedor')
            ->when($request->buscar, fn ($q) => $q
                ->where('numero', 'ilike', "%{$request->buscar}%")
                ->orWhere('proveedor_nombre', 'ilike', "%{$request->buscar}%"))
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json(OrdenCompraResource::collection($ordenes)->response()->getData(true));
    }

    public function show(OrdenCompra $ordenCompra): JsonResponse
    {
        return response()->json(['data' => new OrdenCompraResource($ordenCompra->load('items'))]);
    }

    public function estado(Request $request, OrdenCompra $ordenCompra): JsonResponse
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,parcial,recibida,anulada',
        ]);

        $ordenCompra->update(['estado' => $request->estado]);

        return response()->json(['data' => new OrdenCompraResource($ordenCompra->fresh())]);
    }

    public function destroy(OrdenCompra $ordenCompra): JsonResponse
    {
        $ordenCompra->delete();

        return response()->json(['message' => 'Orden de compra eliminada.']);
    }
}
