<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UnidadMedidaResource;
use App\Models\UnidadMedida;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $unidades = UnidadMedida::orderBy('nombre')
            ->paginate($request->integer('per_page', 50));

        return response()->json(UnidadMedidaResource::collection($unidades)->response()->getData(true));
    }

    public function show(UnidadMedida $unidadMedida): JsonResponse
    {
        return response()->json(['data' => new UnidadMedidaResource($unidadMedida)]);
    }

    public function store(Request $request): JsonResponse
    {
        $empresa = $request->attributes->get('empresa');

        $data = $request->validate([
            'codigo' => 'required|string|max:10',
            'nombre' => 'required|string|max:100',
        ]);

        $data['empresa_id'] = $empresa->id;

        $unidad = UnidadMedida::create($data);

        return response()->json(['data' => new UnidadMedidaResource($unidad)], 201);
    }

    public function update(Request $request, UnidadMedida $unidadMedida): JsonResponse
    {
        $data = $request->validate([
            'codigo' => 'nullable|string|max:10',
            'nombre' => 'nullable|string|max:100',
            'activo' => 'boolean',
        ]);

        $unidadMedida->update($data);

        return response()->json(['data' => new UnidadMedidaResource($unidadMedida->fresh())]);
    }

    public function destroy(UnidadMedida $unidadMedida): JsonResponse
    {
        $unidadMedida->delete();

        return response()->json(['message' => 'Unidad de medida eliminada.']);
    }
}
