<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categorias = Categoria::orderBy('nombre')
            ->paginate($request->integer('per_page', 50));

        return response()->json(CategoriaResource::collection($categorias)->response()->getData(true));
    }

    public function show(Categoria $categoria): JsonResponse
    {
        return response()->json(['data' => new CategoriaResource($categoria)]);
    }

    public function store(Request $request): JsonResponse
    {
        $empresa = $request->attributes->get('empresa');

        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
        ]);

        $data['empresa_id'] = $empresa->id;

        $categoria = Categoria::create($data);

        return response()->json(['data' => new CategoriaResource($categoria)], 201);
    }

    public function update(Request $request, Categoria $categoria): JsonResponse
    {
        $data = $request->validate([
            'nombre'      => 'nullable|string|max:100',
            'descripcion' => 'nullable|string',
            'activo'      => 'boolean',
        ]);

        $categoria->update($data);

        return response()->json(['data' => new CategoriaResource($categoria->fresh())]);
    }

    public function destroy(Categoria $categoria): JsonResponse
    {
        $categoria->update(['activo' => false]);
        $categoria->delete(); // SoftDelete

        return response()->json(['message' => 'Categoría archivada correctamente.']);
    }
}
