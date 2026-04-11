<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClienteResource;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $clientes = Cliente::query()
            ->when($request->buscar, fn ($q) => $q->buscar($request->buscar))
            ->when($request->activo !== null, fn ($q) => $q->where('activo', filter_var($request->activo, FILTER_VALIDATE_BOOLEAN)))
            ->when($request->tipo, fn ($q) => $q->where('tipo_persona', $request->tipo))
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 20));

        return response()->json(ClienteResource::collection($clientes)->response()->getData(true));
    }

    public function show(Cliente $cliente): JsonResponse
    {
        return response()->json(['data' => new ClienteResource($cliente)]);
    }

    public function store(Request $request): JsonResponse
    {
        $empresa = $request->attributes->get('empresa');

        $data = $request->validate([
            'tipo_persona'        => 'required|in:natural,juridica',
            'tipo_documento'      => 'required|in:CC,NIT,CE,PP,TI,PEP',
            'numero_documento'    => 'required|string|max:20|unique:clientes,numero_documento',
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'nullable|string|max:255',
            'nombres'             => 'nullable|string|max:100',
            'apellidos'           => 'nullable|string|max:100',
            'regimen'             => 'required|in:simple,responsable_iva',
            'responsable_iva'     => 'boolean',
            'retefuente_pct'      => 'numeric|min:0|max:100',
            'reteiva_pct'         => 'numeric|min:0|max:100',
            'reteica_pct'         => 'numeric|min:0|max:100',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'plazo_pago'          => 'integer|min:0|max:365',
            'cupo_credito'        => 'numeric|min:0',
            'observaciones'       => 'nullable|string',
        ]);

        $data['empresa_id'] = $empresa->id;

        $cliente = Cliente::create($data);

        return response()->json(['data' => new ClienteResource($cliente)], 201);
    }

    public function update(Request $request, Cliente $cliente): JsonResponse
    {
        $data = $request->validate([
            'razon_social'        => 'nullable|string|max:255',
            'nombres'             => 'nullable|string|max:100',
            'apellidos'           => 'nullable|string|max:100',
            'regimen'             => 'in:simple,responsable_iva',
            'responsable_iva'     => 'boolean',
            'retefuente_pct'      => 'numeric|min:0|max:100',
            'reteiva_pct'         => 'numeric|min:0|max:100',
            'reteica_pct'         => 'numeric|min:0|max:100',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'plazo_pago'          => 'integer|min:0|max:365',
            'cupo_credito'        => 'numeric|min:0',
            'activo'              => 'boolean',
            'observaciones'       => 'nullable|string',
        ]);

        $cliente->update($data);

        return response()->json(['data' => new ClienteResource($cliente->fresh())]);
    }

    public function destroy(Cliente $cliente): JsonResponse
    {
        $cliente->delete();

        return response()->json(['message' => 'Cliente eliminado.']);
    }
}
