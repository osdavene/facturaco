<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $proveedores = Proveedor::query()
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->when($request->estado === 'activo',   fn($q) => $q->where('activo', true))
            ->when($request->estado === 'inactivo', fn($q) => $q->where('activo', false))
            ->orderBy('razon_social')
            ->paginate(15)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_documento'      => 'required|in:NIT,CC,CE',
            'numero_documento'    => 'required|string|max:20|unique:proveedores',
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'required|string|max:255',
            'nombre_contacto'     => 'nullable|string|max:150',
            'cargo_contacto'      => 'nullable|string|max:100',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'regimen'             => 'required|in:simple,responsable_iva',
            'gran_contribuyente'  => 'boolean',
            'autoretenedor'       => 'boolean',
            'retefuente_pct'      => 'numeric|min:0|max:100',
            'reteiva_pct'         => 'numeric|min:0|max:100',
            'reteica_pct'         => 'numeric|min:0|max:100',
            'plazo_pago'          => 'integer|min:0|max:365',
            'cuenta_bancaria'     => 'nullable|string|max:30',
            'banco'               => 'nullable|string|max:100',
            'tipo_cuenta'         => 'nullable|in:ahorros,corriente',
            'observaciones'       => 'nullable|string',
        ]);

        $data['gran_contribuyente'] = $request->boolean('gran_contribuyente');
        $data['autoretenedor']      = $request->boolean('autoretenedor');

        Proveedor::create($data);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor creado correctamente.');
    }

    public function show(Proveedor $proveedor)
    {
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit(Proveedor $proveedor)
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $data = $request->validate([
            'tipo_documento'      => 'required|in:NIT,CC,CE',
            'numero_documento'    => 'required|string|max:20|unique:proveedores,numero_documento,'.$proveedor->id,
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'required|string|max:255',
            'nombre_contacto'     => 'nullable|string|max:150',
            'cargo_contacto'      => 'nullable|string|max:100',
            'email'               => 'nullable|email|max:255',
            'telefono'            => 'nullable|string|max:20',
            'celular'             => 'nullable|string|max:20',
            'departamento'        => 'nullable|string|max:100',
            'municipio'           => 'nullable|string|max:100',
            'direccion'           => 'nullable|string|max:255',
            'regimen'             => 'required|in:simple,responsable_iva',
            'gran_contribuyente'  => 'boolean',
            'autoretenedor'       => 'boolean',
            'retefuente_pct'      => 'numeric|min:0|max:100',
            'reteiva_pct'         => 'numeric|min:0|max:100',
            'reteica_pct'         => 'numeric|min:0|max:100',
            'plazo_pago'          => 'integer|min:0|max:365',
            'cuenta_bancaria'     => 'nullable|string|max:30',
            'banco'               => 'nullable|string|max:100',
            'tipo_cuenta'         => 'nullable|in:ahorros,corriente',
            'activo'              => 'boolean',
            'observaciones'       => 'nullable|string',
        ]);

        $data['gran_contribuyente'] = $request->boolean('gran_contribuyente');
        $data['autoretenedor']      = $request->boolean('autoretenedor');
        $data['activo']             = $request->boolean('activo');

        $proveedor->update($data);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->delete();
        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor eliminado correctamente.');
    }
}