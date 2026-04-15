<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('productos')
            ->orderBy('nombre')
            ->paginate(20);

        return view('categorias.index', compact('categorias'));
    }

    public function create()
    {
        return view('categorias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe una categoría con ese nombre.',
            'nombre.max'      => 'El nombre no puede tener más de 100 caracteres.',
        ]);

        $data['nombre'] = strtoupper(trim($data['nombre']));
        if (!empty($data['descripcion'])) {
            $data['descripcion'] = strtoupper(trim($data['descripcion']));
        }
        $data['activo'] = true;

        Categoria::create($data);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria)
    {
        // Se carga withCount para que la vista pueda mostrar cuántos productos tiene
        $categoria->loadCount('productos');

        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:100|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string|max:255',
            'activo'      => 'boolean',
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.unique'   => 'Ya existe otra categoría con ese nombre.',
        ]);

        $data['nombre'] = strtoupper(trim($data['nombre']));
        if (!empty($data['descripcion'])) {
            $data['descripcion'] = strtoupper(trim($data['descripcion']));
        }
        $data['activo'] = $request->boolean('activo');

        $categoria->update($data);

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria)
    {
        if ($categoria->productos()->count() > 0) {
            return back()->with('error',
                'No se puede archivar la categoría "' . $categoria->nombre .
                '" porque tiene ' . $categoria->productos()->count() . ' producto(s) asociado(s). Archiva primero los productos.'
            );
        }

        $nombre = $categoria->nombre;
        $categoria->update(['activo' => false]);
        $categoria->delete(); // SoftDelete

        return redirect()->route('categorias.index')
            ->with('success', 'Categoría "' . $nombre . '" archivada. El registro se conserva.');
    }
}