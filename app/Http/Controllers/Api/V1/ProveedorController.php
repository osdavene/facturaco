<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProveedorResource;
use App\Models\Proveedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $proveedores = Proveedor::query()
            ->when($request->buscar, fn ($q) => $q->buscar($request->buscar))
            ->when($request->activo !== null, fn ($q) => $q->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('razon_social')
            ->paginate($request->integer('per_page', 20));

        return response()->json(ProveedorResource::collection($proveedores)->response()->getData(true));
    }

    public function show(Proveedor $proveedor): JsonResponse
    {
        return response()->json(['data' => new ProveedorResource($proveedor)]);
    }

    public function store(Request $request): JsonResponse
    {
        $empresa = $request->attributes->get('empresa');

        $data = $request->validate([
            'tipo_documento'      => 'required|in:NIT,CC,CE',
            'numero_documento'    => 'required|string|max:20|unique:proveedores,numero_documento',
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'required|string|max:255',
            'nombre_contacto'     => 'nullable|string|max:150',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'regimen'             => 'required|in:simple,responsable_iva',
            'retefuente_pct'      => 'numeric|min:0|max:100',
            'reteiva_pct'         => 'numeric|min:0|max:100',
            'reteica_pct'         => 'numeric|min:0|max:100',
            'plazo_pago'          => 'integer|min:0|max:365',
            'observaciones'       => 'nullable|string',
        ]);

        $data['empresa_id'] = $empresa->id;

        $proveedor = Proveedor::create($data);

        return response()->json(['data' => new ProveedorResource($proveedor)], 201);
    }

    public function update(Request $request, Proveedor $proveedor): JsonResponse
    {
        $data = $request->validate([
            'razon_social'    => 'nullable|string|max:255',
            'nombre_contacto' => 'nullable|string|max:150',
            'email'           => 'nullable|email|max:255',
            'telefono'        => 'nullable|string|max:20',
            'celular'         => 'nullable|string|max:20',
            'departamento'    => 'nullable|string|max:100',
            'municipio'       => 'nullable|string|max:100',
            'direccion'       => 'nullable|string|max:255',
            'plazo_pago'      => 'integer|min:0|max:365',
            'activo'          => 'boolean',
            'observaciones'   => 'nullable|string',
        ]);

        $proveedor->update($data);

        return response()->json(['data' => new ProveedorResource($proveedor->fresh())]);
    }

    public function destroy(Proveedor $proveedor): JsonResponse
    {
        $proveedor->update(['activo' => false]);
        $proveedor->delete(); // SoftDelete

        return response()->json(['message' => 'Proveedor archivado correctamente.']);
    }
}
