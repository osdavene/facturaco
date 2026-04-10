<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index()
    {
        $empresaId = session('empresa_activa_id');

        $usuarios = User::whereHas('empresas', fn($q) =>
                        $q->where('empresa_id', $empresaId)
                    )
                    ->with('roles')
                    ->orderBy('name')
                    ->paginate(15);

        $totalUsuarios = $usuarios->total();
        $roles         = Role::withCount('users')->get();

        return view('usuarios.index', compact('usuarios', 'totalUsuarios', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('usuarios.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'rol'      => 'required|exists:roles,name',
        ]);

        $usuario = User::create([
            'name'     => strtoupper($request->name),
            'email'    => strtolower($request->email),
            'password' => Hash::make($request->password),
        ]);

        $usuario->assignRole($request->rol);

        // Vincular a la empresa activa como operador
        $usuario->empresas()->attach(session('empresa_activa_id'), [
            'rol'    => 'operador',
            'activo' => true,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $roles = Role::orderBy('name')->get();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:8|confirmed',
            'rol'      => 'required|exists:roles,name',
        ]);

        $usuario->update([
            'name'  => strtoupper($request->name),
            'email' => strtolower($request->email),
        ]);

        if ($request->filled('password')) {
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        $usuario->syncRoles([$request->rol]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Desvincular de la empresa activa en lugar de eliminar globalmente
        $usuario->empresas()->detach(session('empresa_activa_id'));

        // Solo eliminar el usuario si no pertenece a ninguna otra empresa
        if ($usuario->empresas()->count() === 0) {
            $usuario->delete();
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario removido de la empresa.');
    }

    public function toggleActivo(User $usuario)
    {
        if ($usuario->id === auth()->id()) {
            return back()->with('error', 'No puedes desactivar tu propio usuario.');
        }

        $empresaId = session('empresa_activa_id');
        $pivot     = $usuario->empresas()->where('empresa_id', $empresaId)->first()?->pivot;

        if ($pivot) {
            $usuario->empresas()->updateExistingPivot($empresaId, [
                'activo' => !$pivot->activo,
            ]);
        }

        return back()->with('success', 'Estado del usuario actualizado.');
    }
}
