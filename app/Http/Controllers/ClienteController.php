<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function __construct()
    {
        // Los permisos se manejan en las rutas
    }

    public function index(Request $request)
    {
        $clientes = Cliente::query()
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->when($request->estado === 'activo',   fn($q) => $q->where('activo', true))
            ->when($request->estado === 'inactivo', fn($q) => $q->where('activo', false))
            ->when($request->tipo,  fn($q) => $q->where('tipo_persona', $request->tipo))
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        return view('clientes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tipo_persona'        => 'required|in:natural,juridica',
            'tipo_documento'      => 'required|in:CC,NIT,CE,PP,TI,PEP',
            'numero_documento'    => 'required|string|max:20|unique:clientes',
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'nullable|string|max:255',
            'nombres'             => 'nullable|string|max:100',
            'apellidos'           => 'nullable|string|max:100',
            'regimen'             => 'required|in:simple,responsable_iva',
            'responsable_iva'     => 'boolean',
            'gran_contribuyente'  => 'boolean',
            'autoretenedor'       => 'boolean',
            'actividad_economica' => 'nullable|string|max:10',
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

        $data['responsable_iva']    = $request->boolean('responsable_iva');
        $data['gran_contribuyente'] = $request->boolean('gran_contribuyente');
        $data['autoretenedor']      = $request->boolean('autoretenedor');

        Cliente::create($data);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente)
    {
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'tipo_persona'        => 'required|in:natural,juridica',
            'tipo_documento'      => 'required|in:CC,NIT,CE,PP,TI,PEP',
            'numero_documento'    => 'required|string|max:20|unique:clientes,numero_documento,'.$cliente->id,
            'digito_verificacion' => 'nullable|string|max:1',
            'razon_social'        => 'nullable|string|max:255',
            'nombres'             => 'nullable|string|max:100',
            'apellidos'           => 'nullable|string|max:100',
            'regimen'             => 'required|in:simple,responsable_iva',
            'responsable_iva'     => 'boolean',
            'gran_contribuyente'  => 'boolean',
            'autoretenedor'       => 'boolean',
            'actividad_economica' => 'nullable|string|max:10',
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

        $data['responsable_iva']    = $request->boolean('responsable_iva');
        $data['gran_contribuyente'] = $request->boolean('gran_contribuyente');
        $data['autoretenedor']      = $request->boolean('autoretenedor');

        $cliente->update($data);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }
}