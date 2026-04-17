<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UsuarioController extends Controller
{
    public function index()
    {
        // Mostrar usuarios de todo el grupo (raíz + filiales)
        $grupoIds = session('empresa_grupo_ids') ?? [session('empresa_activa_id')];

        $usuarios = User::where('is_superadmin', false)
                    ->whereHas('empresas', fn($q) =>
                        $q->whereIn('empresa_id', $grupoIds)
                    )
                    ->with([
                        'roles',
                        'empresas' => fn($q) => $q->whereIn('empresa_id', $grupoIds),
                    ])
                    ->orderBy('name')
                    ->paginate(15);

        $totalUsuarios = $usuarios->total();
        $roles         = Role::withCount('users')->get();

        return view('usuarios.index', compact('usuarios', 'totalUsuarios', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();

        // Empresas del grupo a las que se puede asignar el usuario
        $grupoIds      = session('empresa_grupo_ids') ?? [session('empresa_activa_id')];
        $empresasGrupo = Empresa::whereIn('id', $grupoIds)->orderBy('razon_social')->get();

        return view('usuarios.create', compact('roles', 'empresasGrupo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|string|min:8|confirmed',
            'rol'         => 'required|exists:roles,name',
            'empresa_ids' => 'required|array|min:1',
            'empresa_ids.*' => 'exists:empresa,id',
        ], [
            'empresa_ids.required' => 'Debes seleccionar al menos una empresa.',
            'empresa_ids.min'      => 'Debes seleccionar al menos una empresa.',
        ]);

        // Verificar que las empresas elegidas pertenecen al grupo del admin
        $grupoIds = session('empresa_grupo_ids') ?? [session('empresa_activa_id')];
        $empresasValidas = array_intersect($request->empresa_ids, $grupoIds);

        $usuario = User::create([
            'name'     => strtoupper($request->name),
            'email'    => strtolower($request->email),
            'password' => Hash::make($request->password),
        ]);

        $usuario->assignRole($request->rol);

        $rolPivot = in_array($request->rol, ['admin', 'propietario']) ? 'admin' : 'operador';

        foreach ($empresasValidas as $empId) {
            $usuario->empresas()->attach($empId, [
                'rol'    => $rolPivot,
                'activo' => true,
            ]);
        }

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
