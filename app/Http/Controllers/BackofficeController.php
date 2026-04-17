<?php

namespace App\Http\Controllers;

use App\Actions\ActualizarModulosEmpresaAction;
use App\Actions\CopiarAdminsDeMatrizAction;
use App\Actions\CrearAdminEmpresaAction;
use App\Models\Empresa;
use App\Models\Modulo;
use App\Models\User;
use App\Services\BackupSqlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BackofficeController extends Controller
{
    public function __construct(
        private CrearAdminEmpresaAction      $crearAdmin,
        private CopiarAdminsDeMatrizAction   $copiarAdmins,
        private ActualizarModulosEmpresaAction $actualizarModulos,
        private BackupSqlService             $backupSql,
    ) {}

    // ── Super Panel ───────────────────────────────────────────────────────

    public function dashboard()
    {
        $totalEmpresas = Empresa::count();
        $totalMatrices = Empresa::whereNull('empresa_padre_id')->count();
        $totalFiliales = Empresa::whereNotNull('empresa_padre_id')->count();
        $totalUsuarios = User::where('is_superadmin', false)->count();

        $empresas = Empresa::whereNull('empresa_padre_id')
            ->with(['filiales.usuarios', 'filiales'])
            ->withCount(['filiales', 'usuarios'])
            ->orderBy('razon_social')
            ->get();

        $todasEmpresas = Empresa::orderBy('razon_social')->get();

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
            'razon_social'     => 'required|string|max:200',
            'nit'              => 'required|string|max:20',
            'email'            => 'nullable|email|max:200',
            'telefono'         => 'nullable|string|max:20',
            'municipio'        => 'nullable|string|max:100',
            'empresa_padre_id' => 'nullable|exists:empresa,id',
        ]);

        $empresa = Empresa::create($data);

        if ($empresa->empresa_padre_id) {
            $this->copiarAdmins->execute($empresa);
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
            'razon_social'     => 'required|string|max:200',
            'nit'              => 'required|string|max:20',
            'email'            => 'nullable|email|max:200',
            'telefono'         => 'nullable|string|max:20',
            'municipio'        => 'nullable|string|max:100',
            'empresa_padre_id' => 'nullable|exists:empresa,id',
        ]);

        if (isset($data['empresa_padre_id']) && $data['empresa_padre_id'] == $empresa->id) {
            return back()->withErrors(['empresa_padre_id' => 'Una empresa no puede ser filial de sí misma.']);
        }

        $empresa->update($data);

        return redirect()->route('backoffice.empresas')->with('success', 'Empresa actualizada.');
    }

    public function empresasDestroy(Empresa $empresa)
    {
        Empresa::where('empresa_padre_id', $empresa->id)->update(['empresa_padre_id' => null]);

        $nombre = $empresa->razon_social;
        $empresa->delete();

        return redirect()->route('backoffice.empresas')->with('success', '"' . $nombre . '" eliminada.');
    }

    // ── Módulos ───────────────────────────────────────────────────────────

    public function modulos(Empresa $empresa)
    {
        $modulos = Modulo::where('activo', true)->orderBy('orden')->orderBy('nombre')->get();

        $modulosActivos = $empresa->modulos()
            ->wherePivot('activo', true)
            ->pluck('modulos.id')
            ->toArray();

        return view('backoffice.empresas.modulos', compact('empresa', 'modulos', 'modulosActivos'));
    }

    public function modulosUpdate(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'modulos'   => 'nullable|array',
            'modulos.*' => 'integer|exists:modulos,id',
        ]);

        $this->actualizarModulos->execute($empresa, $data['modulos'] ?? []);

        return redirect()
            ->route('backoffice.empresas.modulos', $empresa)
            ->with('success', 'Módulos actualizados correctamente para ' . $empresa->razon_social . '.');
    }

    // ── Admin de empresa ──────────────────────────────────────────────────

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
            'rol'      => 'required|exists:roles,name',
        ]);

        $user = $this->crearAdmin->execute($data, $empresa);

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
        $todasEmpresas   = Empresa::orderBy('razon_social')->get();
        $empresasUsuario = $usuario->empresas()->pluck('empresa_id')->toArray();

        return view('backoffice.usuarios.editar', compact('usuario', 'todasEmpresas', 'empresasUsuario'));
    }

    public function usuarioUpdate(Request $request, User $usuario)
    {
        $request->validate([
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users,email,' . $usuario->id,
            'empresa_ids'   => 'nullable|array',
            'empresa_ids.*' => 'exists:empresa,id',
            'roles'         => 'nullable|array',
        ]);

        $usuario->update([
            'name'  => strtoupper($request->name),
            'email' => strtolower($request->email),
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'min:8|confirmed']);
            $usuario->update(['password' => Hash::make($request->password)]);
        }

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

        return redirect()->route('backoffice.usuarios')->with('success', '"' . $nombre . '" eliminado.');
    }

    // ── Impersonación ─────────────────────────────────────────────────────

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

    // ── Backup ────────────────────────────────────────────────────────────

    public function backupIndex()
    {
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
        $sql    = $this->backupSql->generar(auth()->user()->name);
        $nombre = 'backoffice_backup_completo_' . now()->format('Y-m-d_His') . '.sql';

        return response($sql, 200, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }
}
