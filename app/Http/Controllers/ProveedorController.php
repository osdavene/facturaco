<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
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

    public function store(StoreProveedorRequest $request)
    {
        $data = $request->validated();

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

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
    {
        $data = $request->validated();

        $data['gran_contribuyente'] = $request->boolean('gran_contribuyente');
        $data['autoretenedor']      = $request->boolean('autoretenedor');
        $data['activo']             = $request->boolean('activo');

        $proveedor->update($data);

        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => false]);
        $proveedor->delete(); // SoftDelete
        return redirect()->route('proveedores.index')
            ->with('success', 'Proveedor archivado. El registro se conserva para trazabilidad.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionó ningún elemento.');
        }
        $count = Proveedor::whereIn('id', $ids)->count();
        Proveedor::whereIn('id', $ids)->update(['activo' => false]);
        Proveedor::whereIn('id', $ids)->delete(); // SoftDelete
        return redirect()->route('proveedores.index')
            ->with('success', "{$count} proveedor(es) archivado(s) correctamente.");
    }
}