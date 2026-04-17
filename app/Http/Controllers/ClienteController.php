<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
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

    public function store(StoreClienteRequest $request)
    {
        $data = $request->validated();

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

    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        $data = $request->validated();

        $data['responsable_iva']    = $request->boolean('responsable_iva');
        $data['gran_contribuyente'] = $request->boolean('gran_contribuyente');
        $data['autoretenedor']      = $request->boolean('autoretenedor');

        $cliente->update($data);

        return redirect()->route('clientes.index')
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);
        $cliente->delete(); // SoftDelete: solo marca deleted_at
        return redirect()->route('clientes.index')
            ->with('success', 'Cliente archivado. El registro se conserva para trazabilidad.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionó ningún elemento.');
        }
        $count = Cliente::whereIn('id', $ids)->count();
        Cliente::whereIn('id', $ids)->update(['activo' => false]);
        Cliente::whereIn('id', $ids)->delete(); // SoftDelete
        return redirect()->route('clientes.index')
            ->with('success', "{$count} cliente(s) archivado(s) correctamente.");
    }
}