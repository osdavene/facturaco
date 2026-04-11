<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmpresaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmpresaController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $empresa = $request->attributes->get('empresa');

        return response()->json(['data' => new EmpresaResource($empresa)]);
    }

    public function update(Request $request): JsonResponse
    {
        $empresa = $request->attributes->get('empresa');

        $data = $request->validate([
            'nombre_comercial'    => 'nullable|string|max:255',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'pie_factura'         => 'nullable|string',
            'terminos_condiciones' => 'nullable|string',
        ]);

        $empresa->update($data);

        return response()->json(['data' => new EmpresaResource($empresa->fresh())]);
    }
}
