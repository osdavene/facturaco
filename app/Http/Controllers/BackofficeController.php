<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BackofficeController extends Controller
{
    // ── Dashboard ─────────────────────────────────────────────────────────

    public function dashboard()
    {
        $totalEmpresas  = Empresa::count();
        $totalMatrices  = Empresa::whereNull('empresa_padre_id')->count();
        $totalFiliales  = Empresa::whereNotNull('empresa_padre_id')->count();
        $totalUsuarios  = User::where('is_superadmin', false)->count();

        $empresas = Empresa::whereNull('empresa_padre_id')
            ->withCount(['filiales', 'usuarios'])
            ->orderBy('razon_social')
            ->get();

        return view('backoffice.dashboard', compact(
            'totalEmpresas', 'totalMatrices', 'totalFiliales', 'totalUsuarios', 'empresas'
        ));
    }

    // ── Empresas ──────────────────────────────────────────────────────────

    public function empresasIndex()
    {
        $empresas = Empresa::whereNull('empresa_padre_id')
            ->with('filiales')
            ->withCount('usuarios')
            ->orderBy('razon_social')
            ->get();

        return view('backoffice.empresas.index', compact('empresas'));
    }

    public function empresasCrear()
    {
        $matrices = Empresa::whereNull('empresa_padre_id')->orderBy('razon_social')->get();
        return view('backoffice.empresas.crear', compact('matrices'));
    }

    public function empresasStore(Request $request)
    {
        $data = $request->validate([
            'razon_social'      => 'required|string|max:200',
            'nit'               => 'required|string|max:20',
            'email'             => 'nullable|email|max:200',
            'telefono'          => 'nullable|string|max:20',
            'municipio'         => 'nullable|string|max:100',
            'empresa_padre_id'  => 'nullable|exists:empresa,id',
        ]);

        $empresa = Empresa::create($data);

        // Si es filial, copiar admins de la matriz automáticamente
        if ($empresa->empresa_padre_id) {
            $this->copiarAdminsDeMatriz($empresa);
        }

        return redirect()->route('backoffice.empresas')
            ->with('success', 'Empresa "' . $empresa->razon_social . '" creada correctamente.');
    }

    public function empresasEditar(Empresa $empresa)
    {
        $matrices = Empresa::whereNull('empresa_padre_id')
            ->where('id', '!=', $empresa->id)
            ->orderBy('razon_social')
            ->get();

        $adminUsuarios = $empresa->usuarios()->wherePivot('rol', 'admin')->get();

        return view('backoffice.empresas.editar', compact('empresa', 'matrices', 'adminUsuarios'));
    }

    public function empresasUpdate(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'razon_social'      => 'required|string|max:200',
            'nit'               => 'required|string|max:20',
            'email'             => 'nullable|email|max:200',
            'telefono'          => 'nullable|string|max:20',
            'municipio'         => 'nullable|string|max:100',
            'empresa_padre_id'  => 'nullable|exists:empresa,id',
        ]);

        // Evitar que una empresa sea filial de sí misma
        if (isset($data['empresa_padre_id']) && $data['empresa_padre_id'] == $empresa->id) {
            return back()->withErrors(['empresa_padre_id' => 'Una empresa no puede ser filial de sí misma.']);
        }

        $empresa->update($data);

        return redirect()->route('backoffice.empresas')
            ->with('success', 'Empresa actualizada.');
    }

    public function empresasDestroy(Empresa $empresa)
    {
        // Reubicar filiales como matrices independientes antes de eliminar
        Empresa::where('empresa_padre_id', $empresa->id)
            ->update(['empresa_padre_id' => null]);

        $nombre = $empresa->razon_social;
        $empresa->delete();

        return redirect()->route('backoffice.empresas')
            ->with('success', '"' . $nombre . '" eliminada.');
    }

    // ── Usuarios admin de empresa ─────────────────────────────────────────

    public function crearAdmin(Empresa $empresa)
    {
        return view('backoffice.empresas.crear-admin', compact('empresa'));
    }

    public function storeAdmin(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'activo'   => true,
        ]);

        // Vincular a la empresa como admin
        $empresa->usuarios()->attach($user->id, ['rol' => 'admin', 'activo' => true]);

        // Si la empresa es una filial, también vincular a la matriz
        if ($empresa->empresa_padre_id) {
            $matriz = $empresa->padre;
            if ($matriz && !$matriz->usuarios()->where('user_id', $user->id)->exists()) {
                $matriz->usuarios()->attach($user->id, ['rol' => 'admin', 'activo' => true]);
            }
        }

        // Si la empresa es matriz, vincular a todas sus filiales
        foreach ($empresa->filiales as $filial) {
            if (!$filial->usuarios()->where('user_id', $user->id)->exists()) {
                $filial->usuarios()->attach($user->id, ['rol' => 'admin', 'activo' => true]);
            }
        }

        return redirect()->route('backoffice.empresas')
            ->with('success', 'Usuario admin "' . $user->name . '" creado y vinculado a ' . $empresa->razon_social . '.');
    }

    // ── Impersonar empresa (entrar como ese cliente) ──────────────────────

    public function impersonar(Empresa $empresa)
    {
        session(['empresa_activa_id' => $empresa->id]);
        session(['backoffice_impersonando' => true]);

        return redirect()->route('dashboard')
            ->with('info', 'Estás viendo la app como: ' . $empresa->razon_social);
    }

    public function salirImpersonar()
    {
        session()->forget(['empresa_activa_id', 'backoffice_impersonando']);
        return redirect()->route('backoffice.dashboard');
    }

    // ── Helpers privados ──────────────────────────────────────────────────

    private function copiarAdminsDeMatriz(Empresa $filial): void
    {
        $matriz = Empresa::find($filial->empresa_padre_id);
        if (!$matriz) return;

        $admins = $matriz->usuarios()->wherePivot('rol', 'admin')->get();
        foreach ($admins as $admin) {
            if (!$filial->usuarios()->where('user_id', $admin->id)->exists()) {
                $filial->usuarios()->attach($admin->id, ['rol' => 'admin', 'activo' => true]);
            }
        }
    }
}
