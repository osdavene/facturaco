<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        $unidades = UnidadMedida::withCount('productos')
            ->orderBy('nombre')
            ->paginate(20);

        return view('unidades.index', compact('unidades'));
    }

    public function create()
    {
        return view('unidades.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:unidades_medida,nombre',
            'abreviatura' => 'required|string|max:10|unique:unidades_medida,abreviatura',
        ], [
            'nombre.required'      => 'El nombre de la unidad es obligatorio.',
            'nombre.unique'        => 'Ya existe una unidad con ese nombre.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.unique'   => 'Ya existe una unidad con esa abreviatura.',
            'abreviatura.max'      => 'La abreviatura no puede tener más de 10 caracteres.',
        ]);

        $data['nombre']      = strtoupper(trim($data['nombre']));
        $data['abreviatura'] = strtoupper(trim($data['abreviatura']));
        $data['activo']      = true;

        UnidadMedida::create($data);

        return redirect()->route('unidades.index')
            ->with('success', 'Unidad de medida creada correctamente.');
    }

    public function edit(UnidadMedida $unidad)
    {
        // Se carga withCount para que la vista pueda mostrar cuántos productos tiene
        $unidad->loadCount('productos');

        return view('unidades.edit', compact('unidad'));
    }

    public function update(Request $request, UnidadMedida $unidad)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:unidades_medida,nombre,' . $unidad->id,
            'abreviatura' => 'required|string|max:10|unique:unidades_medida,abreviatura,' . $unidad->id,
            'activo'      => 'boolean',
        ], [
            'nombre.required'      => 'El nombre de la unidad es obligatorio.',
            'nombre.unique'        => 'Ya existe otra unidad con ese nombre.',
            'abreviatura.required' => 'La abreviatura es obligatoria.',
            'abreviatura.unique'   => 'Ya existe otra unidad con esa abreviatura.',
        ]);

        $data['nombre']      = strtoupper(trim($data['nombre']));
        $data['abreviatura'] = strtoupper(trim($data['abreviatura']));
        $data['activo']      = $request->boolean('activo');

        $unidad->update($data);

        return redirect()->route('unidades.index')
            ->with('success', 'Unidad de medida actualizada correctamente.');
    }

    public function destroy(UnidadMedida $unidad)
    {
        if ($unidad->productos()->count() > 0) {
            return back()->with('error',
                'No se puede archivar la unidad "' . $unidad->nombre .
                '" porque tiene ' . $unidad->productos()->count() . ' producto(s) asociado(s). Archiva primero los productos.'
            );
        }

        $nombre = $unidad->nombre;
        $unidad->update(['activo' => false]);
        $unidad->delete(); // SoftDelete

        return redirect()->route('unidades.index')
            ->with('success', 'Unidad "' . $nombre . '" archivada. El registro se conserva.');
    }
}