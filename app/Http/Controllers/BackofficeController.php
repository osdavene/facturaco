<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\User;
use App\Http\Controllers\EmpresaSelectorController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BackofficeController extends Controller
{
    // ── Super Panel (dashboard unificado) ────────────────────────────────

    public function dashboard()
    {
        $totalEmpresas  = Empresa::count();
        $totalMatrices  = Empresa::whereNull('empresa_padre_id')->count();
        $totalFiliales  = Empresa::whereNotNull('empresa_padre_id')->count();
        $totalUsuarios  = User::where('is_superadmin', false)->count();

        // Todas las empresas con sus filiales y conteo de usuarios
        $empresas = Empresa::whereNull('empresa_padre_id')
            ->with(['filiales.usuarios', 'filiales'])
            ->withCount(['filiales', 'usuarios'])
            ->orderBy('razon_social')
            ->get();

        // Todas las filiales sueltas también (por si se usa en tab empresas)
        $todasEmpresas = Empresa::orderBy('razon_social')->get();

        // Usuarios con sus empresas
        $usuarios = User::where('is_superadmin', false)
            ->with(['empresas', 'roles'])
            ->orderBy('name')
            ->paginate(20, ['*'], 'usuarios_page');

        return view('backoffice.panel', compact(
            'totalEmpresas', 'totalMatrices', 'totalFiliales', 'totalUsuarios',
            'empresas', 'todasEmpresas', 'usuarios'
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

    // ── Usuarios (gestión global) ─────────────────────────────────────────

    public function usuariosIndex()
    {
        $usuarios = User::where('is_superadmin', false)
            ->with(['empresas', 'roles'])
            ->orderBy('name')
            ->paginate(25);

        return view('backoffice.usuarios.index', compact('usuarios'));
    }

    public function usuarioEditar(User $usuario)
    {
        $todasEmpresas = Empresa::orderBy('razon_social')->get();
        $empresasUsuario = $usuario->empresas()->pluck('empresa_id')->toArray();

        return view('backoffice.usuarios.editar', compact('usuario', 'todasEmpresas', 'empresasUsuario'));
    }

    public function usuarioUpdate(Request $request, User $usuario)
    {
        $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => 'required|email|unique:users,email,' . $usuario->id,
            'empresa_ids' => 'nullable|array',
            'empresa_ids.*' => 'exists:empresa,id',
            'roles'       => 'nullable|array',
        ]);

        $usuario->update([
            'name'  => strtoupper($request->name),
            'email' => strtolower($request->email),
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        // Actualizar empresas asignadas
        $nuevasEmpresas = [];
        foreach ($request->empresa_ids ?? [] as $empId) {
            $rol = in_array($empId, $request->admins ?? []) ? 'admin' : 'operador';
            $nuevasEmpresas[$empId] = ['rol' => $rol, 'activo' => true];
        }
        $usuario->empresas()->sync($nuevasEmpresas);

        return redirect()->route('backoffice.usuarios')
            ->with('success', 'Usuario "' . $usuario->name . '" actualizado.');
    }

    public function usuarioDestroy(User $usuario)
    {
        $nombre = $usuario->name;
        $usuario->empresas()->detach();
        $usuario->delete();

        return redirect()->route('backoffice.usuarios')
            ->with('success', '"' . $nombre . '" eliminado.');
    }

    // ── Impersonar empresa (entrar como ese cliente) ──────────────────────

    public function impersonar(Empresa $empresa)
    {
        EmpresaSelectorController::establecerSesionEmpresa($empresa);
        session(['backoffice_impersonando' => true]);

        return redirect()->route('dashboard')
            ->with('info', 'Estás viendo la app como: ' . $empresa->razon_social);
    }

    public function salirImpersonar()
    {
        session()->forget(['empresa_activa_id', 'backoffice_impersonando']);
        return redirect()->route('backoffice.dashboard');
    }

    // ── Backup general de la plataforma (solo superadmin) ────────────────

    public function backupIndex()
    {
        // Conteo total por tabla (sin filtro de empresa)
        $tablas = [
            'empresa'                => 'Empresas',
            'users'                  => 'Usuarios',
            'clientes'               => 'Clientes',
            'proveedores'            => 'Proveedores',
            'productos'              => 'Productos',
            'categorias'             => 'Categorías',
            'unidades_medida'        => 'Unidades de Medida',
            'facturas'               => 'Facturas',
            'factura_items'          => 'Ítems de Facturas',
            'cotizaciones'           => 'Cotizaciones',
            'cotizacion_items'       => 'Ítems de Cotizaciones',
            'ordenes_compra'         => 'Órdenes de Compra',
            'orden_compra_items'     => 'Ítems de Órdenes',
            'recibos_caja'           => 'Recibos de Caja',
            'remisiones'             => 'Remisiones',
            'remision_items'         => 'Ítems de Remisiones',
            'movimientos_inventario' => 'Movimientos de Inventario',
            'login_logs'             => 'Accesos',
        ];

        $conteos = [];
        foreach (array_keys($tablas) as $tabla) {
            try {
                $conteos[$tabla] = DB::table($tabla)->count();
            } catch (\Exception) {
                $conteos[$tabla] = 0;
            }
        }

        $totalRegistros = array_sum($conteos);

        return view('backoffice.backup.index', compact('tablas', 'conteos', 'totalRegistros'));
    }

    public function backupDescargar()
    {
        $tablas = [
            'empresa', 'empresa_user', 'users',
            'clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida',
            'facturas', 'factura_items',
            'cotizaciones', 'cotizacion_items',
            'ordenes_compra', 'orden_compra_items',
            'recibos_caja', 'remisiones', 'remision_items',
            'movimientos_inventario', 'login_logs',
        ];

        $sql  = "-- ================================================\n";
        $sql .= "-- BACKUP COMPLETO — FacturaCO (BackOffice)\n";
        $sql .= "-- Fecha:        " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "-- Generado por: " . auth()->user()->name . "\n";
        $sql .= "-- Base de datos: PostgreSQL\n";
        $sql .= "-- ADVERTENCIA: restaurar este archivo reemplaza todos los datos.\n";
        $sql .= "-- ================================================\n\n";
        $sql .= "SET client_encoding = 'UTF8';\n";
        $sql .= "SET standard_conforming_strings = on;\n\n";

        foreach ($tablas as $tabla) {
            try {
                $filas = DB::table($tabla)->get();

                $sql .= "-- ────────────────────────────────────────\n";
                $sql .= "-- Tabla: {$tabla} ({$filas->count()} registros)\n";
                $sql .= "-- ────────────────────────────────────────\n";

                if ($filas->isEmpty()) {
                    $sql .= "-- (sin datos)\n\n";
                    continue;
                }

                foreach ($filas as $fila) {
                    $cols    = array_keys((array) $fila);
                    $colsSql = implode(', ', array_map(fn($c) => '"' . $c . '"', $cols));
                    $vals    = array_map(function ($v) {
                        if (is_null($v))                    return 'NULL';
                        if (is_bool($v))                    return $v ? 'TRUE' : 'FALSE';
                        if (is_int($v) || is_float($v))     return $v;
                        $v = str_replace("'", "''", (string) $v);
                        return "'" . $v . "'";
                    }, (array) $fila);
                    $valsSql = implode(', ', $vals);
                    $sql .= "INSERT INTO \"{$tabla}\" ({$colsSql}) VALUES ({$valsSql});\n";
                }

                $sql .= "\n";
            } catch (\Exception $e) {
                $sql .= "-- ERROR en {$tabla}: " . $e->getMessage() . "\n\n";
            }
        }

        $nombre = 'backoffice_backup_completo_' . now()->format('Y-m-d_His') . '.sql';

        return response($sql, 200, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
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
