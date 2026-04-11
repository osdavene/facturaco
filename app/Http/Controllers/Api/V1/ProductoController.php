<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductoResource;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $productos = Producto::with('categoria')
            ->when($request->buscar, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('nombre', 'ilike', "%{$request->buscar}%")
                  ->orWhere('codigo', 'ilike', "%{$request->buscar}%");
            }))
            ->when($request->activo !== null, fn ($q) => $q->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->tipo, fn ($q) => $q->where('tipo', $request->tipo))
            ->when($request->categoria_id, fn ($q) => $q->where('categoria_id', $request->categoria_id))
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 20));

        return response()->json(ProductoResource::collection($productos)->response()->getData(true));
    }

    public function show(Producto $producto): JsonResponse
    {
        return response()->json(['data' => new ProductoResource($producto->load('categoria'))]);
    }

    public function store(Request $request): JsonResponse
    {
        $empresa = $request->attributes->get('empresa');

        $data = $request->validate([
            'codigo'        => 'required|string|max:50|unique:productos,codigo',
            'codigo_barras' => 'nullable|string|max:50',
            'nombre'        => 'required|string|max:255',
            'descripcion'   => 'nullable|string',
            'tipo'          => 'required|in:producto,servicio',
            'precio_venta'  => 'required|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'iva_pct'       => 'numeric|min:0|max:100',
            'stock_actual'  => 'numeric|min:0',
            'stock_minimo'  => 'numeric|min:0',
            'unidad'        => 'nullable|string|max:20',
            'categoria_id'  => 'nullable|exists:categorias,id',
            'activo'        => 'boolean',
        ]);

        $data['empresa_id'] = $empresa->id;

        $producto = Producto::create($data);

        return response()->json(['data' => new ProductoResource($producto)], 201);
    }

    public function update(Request $request, Producto $producto): JsonResponse
    {
        $data = $request->validate([
            'nombre'        => 'nullable|string|max:255',
            'descripcion'   => 'nullable|string',
            'precio_venta'  => 'nullable|numeric|min:0',
            'precio_compra' => 'nullable|numeric|min:0',
            'iva_pct'       => 'nullable|numeric|min:0|max:100',
            'stock_minimo'  => 'nullable|numeric|min:0',
            'unidad'        => 'nullable|string|max:20',
            'categoria_id'  => 'nullable|exists:categorias,id',
            'activo'        => 'boolean',
        ]);

        $producto->update($data);

        return response()->json(['data' => new ProductoResource($producto->fresh())]);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        $producto->delete();

        return response()->json(['message' => 'Producto eliminado.']);
    }
}
