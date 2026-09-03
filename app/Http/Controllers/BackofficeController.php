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
        $totalPlanes   = \App\Models\Plan::count();
        $totalFacturasDian = \App\Models\Factura::where('enviada_dian', true)->count();
        $proveedorActivo   = \App\Models\ConfiguracionPlataforma::get('dian_proveedor', config('dian.proveedor', 'factus'));
        $factusAmbiente    = \App\Models\ConfiguracionPlataforma::get('dian_factus_ambiente', config('dian.factus.ambiente', 'sandbox'));

        $empresas = Empresa::whereNull('empresa_padre_id')
            ->with(['filiales.usuarios', 'filiales.plan', 'plan'])
            ->withCount(['filiales', 'usuarios'])
            ->orderBy('razon_social')
            ->get();

        $todasEmpresas = Empresa::with('plan')->orderBy('razon_social')->get();
        $planes        = \App\Models\Plan::withCount('empresas')->orderBy('orden')->get();

        $usuarios = User::where('is_superadmin', false)
            ->with(['empresas', 'roles'])
            ->orderBy('name')
            ->paginate(20, ['*'], 'usuarios_page');

        return view('backoffice.panel', compact(
            'totalEmpresas', 'totalMatrices', 'totalFiliales', 'totalUsuarios',
            'totalPlanes', 'totalFacturasDian', 'proveedorActivo', 'factusAmbiente',
            'empresas', 'todasEmpresas', 'planes', 'usuarios'
        ));
    }

    // ── Empresas ──────────────────────────────────────────────────────────

    public function empresasIndex()
    {
        $empresas = Empresa::whereNull('empresa_padre_id')
            ->with(['filiales.plan', 'plan'])
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
        $planes = \App\Models\Plan::where('activo', true)->orderBy('orden')->get();

        return view('backoffice.empresas.editar', compact('empresa', 'matrices', 'adminUsuarios', 'planes'));
    }

    public function empresasUpdate(Request $request, Empresa $empresa)
    {
        $data = $request->validate([
            'razon_social'               => 'required|string|max:200',
            'nit'                        => 'required|string|max:20',
            'email'                      => 'nullable|email|max:200',
            'telefono'                   => 'nullable|string|max:20',
            'municipio'                  => 'nullable|string|max:100',
            'empresa_padre_id'           => 'nullable|exists:empresa,id',
            'plan_id'                    => 'nullable|exists:planes,id',
            'plan_vencimiento'           => 'nullable|date',
            'plan_facturas_adicionales'  => 'nullable|integer|min:0',
        ]);

        if (isset($data['empresa_padre_id']) && $data['empresa_padre_id'] == $empresa->id) {
            return back()->withErrors(['empresa_padre_id' => 'Una empresa no puede ser filial de sí misma.']);
        }

        $empresa->update($data);

        return redirect()->route('backoffice.empresas')->with('success', 'Empresa y plan actualizados.');
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

    // ── Gestión DIAN & Folios API ──────────────────────────────────────────

    public function dianIndex()
    {
        $totalFacturasDian = \App\Models\Factura::where('enviada_dian', true)->count();
        $totalFacturasMes  = \App\Models\Factura::where('enviada_dian', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $empresas = Empresa::withCount([
            'facturas as facturas_dian_total' => fn($q) => $q->where('enviada_dian', true),
            'facturas as facturas_dian_mes'   => fn($q) => $q->where('enviada_dian', true)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year),
        ])->orderBy('razon_social')->get();

        $proveedorActivo = \App\Models\ConfiguracionPlataforma::get('dian_proveedor', config('dian.proveedor', 'factus'));
        $factusAmbiente  = \App\Models\ConfiguracionPlataforma::get('dian_factus_ambiente', config('dian.factus.ambiente', 'sandbox'));
        $factusClientId  = \App\Models\ConfiguracionPlataforma::get('dian_factus_client_id', config('dian.factus.client_id', ''));
        $factusUsername  = \App\Models\ConfiguracionPlataforma::get('dian_factus_username', config('dian.factus.username', ''));
        $factusToken     = \App\Models\ConfiguracionPlataforma::get('dian_factus_token', config('dian.factus.api_token', ''));
        $factusRangeId   = \App\Models\ConfiguracionPlataforma::get('dian_factus_range_id', config('dian.factus.numbering_range_id', 1));

        return view('backoffice.dian.index', compact(
            'totalFacturasDian', 'totalFacturasMes', 'empresas',
            'proveedorActivo', 'factusAmbiente', 'factusClientId',
            'factusUsername', 'factusToken', 'factusRangeId'
        ));
    }

    public function dianGuardar(Request $request)
    {
        $data = $request->validate([
            'dian_proveedor'        => 'required|in:factus,directo',
            'dian_factus_ambiente'  => 'required|in:sandbox,produccion',
            'dian_factus_client_id' => 'nullable|string|max:255',
            'dian_factus_client_secret' => 'nullable|string|max:255',
            'dian_factus_username'  => 'nullable|string|max:255',
            'dian_factus_password'  => 'nullable|string|max:255',
            'dian_factus_token'     => 'nullable|string|max:1000',
            'dian_factus_range_id'  => 'nullable|integer',
        ]);

        foreach ($data as $key => $val) {
            if ($val !== null && $val !== '') {
                \App\Models\ConfiguracionPlataforma::set($key, $val, 'dian');
            } elseif (in_array($key, ['dian_factus_token', 'dian_factus_client_secret', 'dian_factus_password'])) {
                // Keep existing secret if not provided
            } else {
                \App\Models\ConfiguracionPlataforma::set($key, '', 'dian');
            }
        }

        return redirect()->route('backoffice.dian')->with('success', 'Configuración de integración DIAN guardada con éxito.');
    }

    public function dianProbar()
    {
        try {
            $dian = app(\App\Services\DianService::class);
            $provider = $dian->getProvider();

            if (! $provider->estaConfigurado()) {
                return back()->with('error', 'Faltan credenciales maestras para conectarse a la API de la DIAN / Factus.');
            }

            if ($provider instanceof \App\Services\Dian\FactusProvider) {
                $token = $provider->obtenerToken();
                if ($token) {
                    return back()->with('success', '¡Conexión exitosa con Factus API! Token de autenticación obtenido correctamente.');
                }
            }

            return back()->with('success', '¡Proveedor DIAN verificado y listo para emitir facturas!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al probar conexión: ' . $e->getMessage());
        }
    }

    // ── Gestión de Planes y Paquetes ───────────────────────────────────────

    public function planesIndex()
    {
        $planes = \App\Models\Plan::withCount('empresas')
            ->orderBy('orden')
            ->orderBy('precio')
            ->get();

        $totalEmpresasConPlan = Empresa::whereNotNull('plan_id')->count();

        return view('backoffice.planes.index', compact('planes', 'totalEmpresasConPlan'));
    }

    public function planesStore(Request $request)
    {
        $data = $request->validate([
            'nombre'               => 'required|string|max:100',
            'descripcion'          => 'nullable|string|max:500',
            'precio'               => 'required|numeric|min:0',
            'duracion_meses'       => 'nullable|integer|min:1',
            'limite_facturas_mes'  => 'nullable|integer|min:1',
            'limite_usuarios'      => 'nullable|integer|min:1',
            'limite_productos'     => 'nullable|integer|min:1',
            'color'                => 'required|string|in:blue,amber,emerald,purple,rose,indigo',
            'soporta_dian'         => 'nullable|boolean',
            'soporta_pos'          => 'nullable|boolean',
            'soporta_nomina'       => 'nullable|boolean',
            'soporta_contabilidad' => 'nullable|boolean',
            'destacado'            => 'nullable|boolean',
            'activo'               => 'nullable|boolean',
            'orden'                => 'nullable|integer',
        ]);

        $data['duracion_meses']       = $data['duracion_meses'] ?? 1;
        $data['soporta_dian']         = $request->boolean('soporta_dian');
        $data['soporta_pos']          = $request->boolean('soporta_pos');
        $data['soporta_nomina']       = $request->boolean('soporta_nomina');
        $data['soporta_contabilidad'] = $request->boolean('soporta_contabilidad');
        $data['destacado']            = $request->boolean('destacado');
        $data['activo']               = $request->boolean('activo', true);

        \App\Models\Plan::create($data);

        return redirect()->route('backoffice.planes')->with('success', 'Plan creado exitosamente.');
    }

    public function planesUpdate(Request $request, \App\Models\Plan $plan)
    {
        $data = $request->validate([
            'nombre'               => 'required|string|max:100',
            'descripcion'          => 'nullable|string|max:500',
            'precio'               => 'required|numeric|min:0',
            'duracion_meses'       => 'nullable|integer|min:1',
            'limite_facturas_mes'  => 'nullable|integer|min:1',
            'limite_usuarios'      => 'nullable|integer|min:1',
            'limite_productos'     => 'nullable|integer|min:1',
            'color'                => 'required|string|in:blue,amber,emerald,purple,rose,indigo',
            'soporta_dian'         => 'nullable|boolean',
            'soporta_pos'          => 'nullable|boolean',
            'soporta_nomina'       => 'nullable|boolean',
            'soporta_contabilidad' => 'nullable|boolean',
            'destacado'            => 'nullable|boolean',
            'activo'               => 'nullable|boolean',
            'orden'                => 'nullable|integer',
        ]);

        $data['duracion_meses']       = $data['duracion_meses'] ?? 1;
        $data['soporta_dian']         = $request->boolean('soporta_dian');
        $data['soporta_pos']          = $request->boolean('soporta_pos');
        $data['soporta_nomina']       = $request->boolean('soporta_nomina');
        $data['soporta_contabilidad'] = $request->boolean('soporta_contabilidad');
        $data['destacado']            = $request->boolean('destacado');
        $data['activo']               = $request->boolean('activo', true);

        $plan->update($data);

        return redirect()->route('backoffice.planes')->with('success', 'Plan actualizado.');
    }

    public function planesDestroy(\App\Models\Plan $plan)
    {
        $nombre = $plan->nombre;
        $plan->empresas()->update(['plan_id' => null]);
        $plan->delete();

        return redirect()->route('backoffice.planes')->with('success', "Plan '{$nombre}' eliminado.");
    }

    // ── Configuración de Correo SMTP & Notificaciones ──────────────────────

    public function correoIndex()
    {
        $mailMailer     = \App\Models\ConfiguracionPlataforma::get('mail_mailer', config('mail.default', 'smtp'));
        $mailHost       = \App\Models\ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host', 'smtp.gmail.com'));
        $mailPort       = \App\Models\ConfiguracionPlataforma::get('mail_port', config('mail.mailers.smtp.port', 587));
        $mailEncryption = \App\Models\ConfiguracionPlataforma::get('mail_encryption', config('mail.mailers.smtp.encryption', 'tls'));
        $mailUsername   = \App\Models\ConfiguracionPlataforma::get('mail_username', config('mail.mailers.smtp.username', ''));
        $mailFromAddress = \App\Models\ConfiguracionPlataforma::get('mail_from_address', config('mail.from.address', ''));
        $mailFromName   = \App\Models\ConfiguracionPlataforma::get('mail_from_name', config('mail.from.name', 'FacCol Notificaciones'));

        $empresasPorVencer = Empresa::whereNotNull('plan_vencimiento')
            ->whereNotNull('plan_id')
            ->with(['plan', 'usuarios'])
            ->orderBy('plan_vencimiento')
            ->get()
            ->map(function ($emp) {
                $venc = \Carbon\Carbon::parse($emp->plan_vencimiento)->startOfDay();
                $emp->dias_restantes = (int) \Carbon\Carbon::today()->diffInDays($venc, false);
                return $emp;
            });

        return view('backoffice.correo.index', compact(
            'mailMailer', 'mailHost', 'mailPort', 'mailEncryption',
            'mailUsername', 'mailFromAddress', 'mailFromName',
            'empresasPorVencer'
        ));
    }

    public function correoGuardar(Request $request)
    {
        $data = $request->validate([
            'mail_mailer'       => 'nullable|string|in:smtp,log,resend',
            'mail_host'         => 'required|string|max:255',
            'mail_port'         => 'required|integer',
            'mail_encryption'   => 'nullable|string|in:tls,ssl,null',
            'mail_username'     => 'nullable|string|max:255',
            'mail_password'     => 'nullable|string|max:255',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name'    => 'required|string|max:255',
        ]);

        // Auto-corregir errores tipográficos (.corn -> .com, srntp -> smtp)
        $data['mail_host'] = str_replace(['.corn', 'srntp'], ['.com', 'smtp'], trim($data['mail_host']));
        $data['mail_username'] = str_replace(['.corn', 'srntp'], ['.com', 'smtp'], trim($data['mail_username'] ?? ''));

        foreach ($data as $key => $val) {
            if ($key === 'mail_password' && (empty($val) || $val === null)) {
                continue; // Conservar clave existente si se deja vacía
            }
            \App\Models\ConfiguracionPlataforma::set($key, $val, 'correo');
        }

        return redirect()->route('backoffice.correo')->with('success', 'Configuración de correo guardada exitosamente.');
    }

    public function correoProbar(Request $request)
    {
        $request->validate([
            'email_destino' => 'required|email',
        ]);

        $host = \App\Models\ConfiguracionPlataforma::get('mail_host', config('mail.mailers.smtp.host'));
        $user = \App\Models\ConfiguracionPlataforma::get('mail_username', config('mail.mailers.smtp.username'));
        $pass = \App\Models\ConfiguracionPlataforma::get('mail_password', config('mail.mailers.smtp.password'));
        $from = \App\Models\ConfiguracionPlataforma::get('mail_from_address', config('mail.from.address', 'onboarding@resend.dev'));
        $name = \App\Models\ConfiguracionPlataforma::get('mail_from_name', config('mail.from.name', 'FacCol'));

        // Si es Resend (API Key o usuario resend), usar directamente la API HTTPS de Resend (Puerto 443 - 100% inmune a bloqueos)
        if (str_contains(strtolower((string)$host), 'resend') || strtolower((string)$user) === 'resend' || str_starts_with((string)$pass, 're_')) {
            $apiKey = trim((string)$pass);
            if (empty($apiKey)) {
                return back()->with('error', 'Debes ingresar tu API Key de Resend (empieza por re_...) en el campo Contraseña.');
            }

            try {
                $response = \Illuminate\Support\Facades\Http::withToken($apiKey)
                    ->acceptJson()
                    ->post('https://api.resend.com/emails', [
                        'from'    => "{$name} <{$from}>",
                        'to'      => [$request->email_destino],
                        'subject' => '✅ Prueba Exitosa de Correo — FacCol (Resend API)',
                        'html'    => "<h2>¡Hola!</h2><p>Este es un correo de prueba enviado exitosamente desde tu plataforma <strong>FacCol</strong> mediante la <strong>API HTTPS de Resend</strong>.</p><p>Fecha y hora: " . now()->format('d/m/Y H:i:s') . "</p>",
                    ]);

                if ($response->successful()) {
                    return back()->with('success', "¡Correo de prueba enviado con éxito a {$request->email_destino} vía Resend HTTPS API!");
                }

                $errorMsg = $response->json('message') ?? ($response->json('error') ?? $response->body());
                return back()->with('error', "Error de Resend API: {$errorMsg}");
            } catch (\Throwable $e) {
                return back()->with('error', 'Error al conectar con Resend API: ' . $e->getMessage());
            }
        }

        try {
            \App\Services\MailConfigService::aplicarConfiguracion();

            \Illuminate\Support\Facades\Mail::raw(
                "¡Hola! Este es un correo de prueba enviado desde tu plataforma FacCol.\n\nTu servidor de correo SMTP está funcionando correctamente.\nFecha y hora: " . now()->format('d/m/Y H:i:s'),
                function ($message) use ($request) {
                    $message->to($request->email_destino)
                            ->subject('✅ Prueba Exitosa de Correo — FacCol');
                }
            );

            return back()->with('success', "¡Correo de prueba enviado con éxito a {$request->email_destino}!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al enviar correo: ' . $e->getMessage());
        }
    }

    public function notificarEmpresaVencimiento(Empresa $empresa)
    {
        try {
            \App\Services\MailConfigService::aplicarConfiguracion();

            $vencimiento = \Carbon\Carbon::parse($empresa->plan_vencimiento ?? now())->startOfDay();
            $diasRestantes = (int) \Carbon\Carbon::today()->diffInDays($vencimiento, false);

            $destinatarios = collect([$empresa->email])
                ->merge($empresa->usuarios->pluck('email'))
                ->filter(fn($e) => filter_var($e, FILTER_VALIDATE_EMAIL))
                ->unique()
                ->values()
                ->toArray();

            if (empty($destinatarios)) {
                return back()->with('error', "La empresa '{$empresa->razon_social}' no tiene correos electrónicos configurados.");
            }

            \Illuminate\Support\Facades\Mail::to($destinatarios)->send(new \App\Mail\PlanVencimientoMail($empresa, $diasRestantes));

            return back()->with('success', "Aviso de vencimiento enviado a {$empresa->razon_social} (" . implode(', ', $destinatarios) . ").");
        } catch (\Throwable $e) {
            return back()->with('error', 'Error enviando notificación: ' . $e->getMessage());
        }
    }
}
