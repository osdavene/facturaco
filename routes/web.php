<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ClienteController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReciboCajaController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\ProximamenteController;
use App\Http\Controllers\RemisionController;
use App\Http\Controllers\ImpuestosController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\NotaCreditoController;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

// ── Remisiones ────────────────────────────────────────────────
Route::get('/remisiones',                        [RemisionController::class, 'index'])        ->name('remisiones.index');
Route::get('/remisiones/crear',                  [RemisionController::class, 'create'])       ->name('remisiones.create');
Route::post('/remisiones',                       [RemisionController::class, 'store'])        ->name('remisiones.store');
Route::get('/remisiones/{remision}',             [RemisionController::class, 'show'])         ->name('remisiones.show');
Route::delete('/remisiones/{remision}',          [RemisionController::class, 'destroy'])      ->name('remisiones.destroy');
Route::patch('/remisiones/{remision}/estado',    [RemisionController::class, 'cambiarEstado'])->name('remisiones.estado');
Route::post('/remisiones/{remision}/convertir',  [RemisionController::class, 'convertir'])    ->name('remisiones.convertir');
Route::get('/remisiones/{remision}/pdf',         [RemisionController::class, 'pdf'])          ->name('remisiones.pdf');

Route::get('/impuestos',     [ImpuestosController::class, 'index'])->name('impuestos.index')->middleware('auth');
Route::get('/impuestos/pdf', [ImpuestosController::class, 'pdf'])  ->name('impuestos.pdf')  ->middleware('auth');

Route::get('/busqueda', [BusquedaController::class, 'buscar'])->name('busqueda')->middleware('auth');

// ── Perfil ────────────────────────────────────────────────────
Route::get('/perfil',           [PerfilController::class, 'index'])         ->name('perfil.index')         ->middleware('auth');
Route::put('/perfil',           [PerfilController::class, 'update'])        ->name('perfil.update')        ->middleware('auth');
Route::put('/perfil/password',  [PerfilController::class, 'updatePassword'])->name('perfil.password')      ->middleware('auth');
Route::post('/perfil/avatar',   [PerfilController::class, 'updateAvatar'])  ->name('perfil.avatar')        ->middleware('auth');
Route::delete('/perfil/avatar', [PerfilController::class, 'deleteAvatar'])  ->name('perfil.avatar.delete') ->middleware('auth');

// ── Excel exports ─────────────────────────────────────────────
Route::get('/reportes/ventas/excel',     [ReporteController::class, 'ventasExcel'])    ->name('reportes.ventas.excel')    ->middleware('auth');
Route::get('/reportes/inventario/excel', [ReporteController::class, 'inventarioExcel'])->name('reportes.inventario.excel')->middleware('auth');
Route::get('/reportes/cartera/excel',    [ReporteController::class, 'carteraExcel'])   ->name('reportes.cartera.excel')   ->middleware('auth');
Route::get('/impuestos/excel',           [ImpuestosController::class, 'excel'])        ->name('impuestos.excel')          ->middleware('auth');

Route::post('/tema', function(\Illuminate\Http\Request $request) {
    $tema = $request->tema === 'light' ? 'light' : 'dark';
    auth()->user()->update(['tema' => $tema]);
    return back();
})->name('tema.cambiar')->middleware('auth');

// ── Página de inicio ──────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
});

// ── Rutas autenticadas ────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Perfil de usuario (Laravel default)
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Dashboard ─────────────────────────────────────────────
    Route::get('/dashboard', function () {
        $empresa = \App\Models\Empresa::obtener();

        $ventasHoy   = \App\Models\Factura::whereDate('fecha_emision', today())
                        ->where('estado', '!=', 'anulada')->sum('total');
        $ventasMes   = \App\Models\Factura::whereMonth('fecha_emision', now()->month)
                        ->whereYear('fecha_emision', now()->year)
                        ->where('estado', '!=', 'anulada')->sum('total');
        $ventasAno   = \App\Models\Factura::whereYear('fecha_emision', now()->year)
                        ->where('estado', '!=', 'anulada')->sum('total');
        $cartera     = \App\Models\Factura::whereIn('estado', ['emitida', 'vencida'])
                        ->sum(\Illuminate\Support\Facades\DB::raw('total - total_pagado'));
        $facturasMes = \App\Models\Factura::whereMonth('fecha_emision', now()->month)
                        ->whereYear('fecha_emision', now()->year)->count();

        $facturasVencidas   = \App\Models\Factura::where('estado', 'vencida')->count();
        $productosStockBajo = \App\Models\Producto::where('activo', true)
                               ->where('es_servicio', false)
                               ->whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        $cotizacionesPend   = \App\Models\Cotizacion::whereIn('estado', ['enviada', 'aceptada'])->count();
        $ordenesPend        = \App\Models\OrdenCompra::where('estado', 'aprobada')->count();

        $ultimasFacturas = \App\Models\Factura::orderByDesc('created_at')->limit(6)->get();

        $ventasPorMes = collect();
        for ($i = 11; $i >= 0; $i--) {
            $fecha = now()->subMonths($i);
            $total = \App\Models\Factura::whereMonth('fecha_emision', $fecha->month)
                      ->whereYear('fecha_emision', $fecha->year)
                      ->where('estado', '!=', 'anulada')->sum('total');
            $ventasPorMes->push([
                'mes'   => $fecha->locale('es')->isoFormat('MMM'),
                'anio'  => $fecha->year,
                'total' => (float) $total,
            ]);
        }

        $ventasSemana = collect();
        for ($i = 6; $i >= 0; $i--) {
            $dia   = now()->subDays($i);
            $total = \App\Models\Factura::whereDate('fecha_emision', $dia)
                      ->where('estado', '!=', 'anulada')->sum('total');
            $ventasSemana->push([
                'dia'   => $dia->locale('es')->isoFormat('ddd D'),
                'total' => (float) $total,
            ]);
        }

        $topClientes = \App\Models\Factura::select(
                            'cliente_nombre',
                            \Illuminate\Support\Facades\DB::raw('SUM(total) as total_mes')
                        )
                        ->whereMonth('fecha_emision', now()->month)
                        ->whereYear('fecha_emision', now()->year)
                        ->where('estado', '!=', 'anulada')
                        ->groupBy('cliente_nombre')
                        ->orderByDesc('total_mes')
                        ->limit(5)->get();

        $topProductos = \App\Models\FacturaItem::select(
                            'descripcion',
                            \Illuminate\Support\Facades\DB::raw('SUM(cantidad) as total_qty'),
                            \Illuminate\Support\Facades\DB::raw('SUM(total) as total_valor')
                        )
                        ->whereHas('factura', fn($q) =>
                            $q->whereMonth('fecha_emision', now()->month)
                              ->whereYear('fecha_emision', now()->year)
                              ->where('estado', '!=', 'anulada')
                        )
                        ->groupBy('descripcion')
                        ->orderByDesc('total_valor')
                        ->limit(5)->get();

        $ventasPorEstado = \App\Models\Factura::select(
                                'estado',
                                \Illuminate\Support\Facades\DB::raw('COUNT(*) as cantidad')
                            )
                            ->whereMonth('fecha_emision', now()->month)
                            ->whereYear('fecha_emision', now()->year)
                            ->groupBy('estado')->get()
                            ->keyBy('estado');

        return view('dashboard', compact(
            'empresa', 'ventasHoy', 'ventasMes', 'ventasAno', 'cartera', 'facturasMes',
            'facturasVencidas', 'productosStockBajo', 'cotizacionesPend', 'ordenesPend',
            'ultimasFacturas', 'ventasSemana', 'ventasPorMes', 'topClientes',
            'topProductos', 'ventasPorEstado'
        ));
    })->middleware('verified')->name('dashboard');

    // ── Clientes ──────────────────────────────────────────────
    Route::get('/clientes',                    [ClienteController::class, 'index'])  ->name('clientes.index');
    Route::get('/clientes/crear',              [ClienteController::class, 'create']) ->name('clientes.create');
    Route::post('/clientes',                   [ClienteController::class, 'store'])  ->name('clientes.store');
    Route::get('/clientes/{cliente}',          [ClienteController::class, 'show'])   ->name('clientes.show');
    Route::get('/clientes/{cliente}/editar',   [ClienteController::class, 'edit'])   ->name('clientes.edit');
    Route::put('/clientes/{cliente}',          [ClienteController::class, 'update']) ->name('clientes.update');
    Route::delete('/clientes/{cliente}',       [ClienteController::class, 'destroy'])->name('clientes.destroy');

    // ── Proveedores ───────────────────────────────────────────
    Route::get('/proveedores',                    [ProveedorController::class, 'index'])  ->name('proveedores.index');
    Route::get('/proveedores/crear',              [ProveedorController::class, 'create']) ->name('proveedores.create');
    Route::post('/proveedores',                   [ProveedorController::class, 'store'])  ->name('proveedores.store');
    Route::get('/proveedores/{proveedor}',        [ProveedorController::class, 'show'])   ->name('proveedores.show');
    Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])   ->name('proveedores.edit');
    Route::put('/proveedores/{proveedor}',        [ProveedorController::class, 'update']) ->name('proveedores.update');
    Route::delete('/proveedores/{proveedor}',     [ProveedorController::class, 'destroy'])->name('proveedores.destroy');

    // ── Inventario ────────────────────────────────────────────
    Route::get('/inventario',                       [ProductoController::class, 'index'])       ->name('inventario.index');
    Route::get('/inventario/crear',                 [ProductoController::class, 'create'])      ->name('inventario.create');
    Route::post('/inventario',                      [ProductoController::class, 'store'])       ->name('inventario.store');
    Route::get('/inventario/{inventario}',          [ProductoController::class, 'show'])        ->name('inventario.show');
    Route::get('/inventario/{inventario}/editar',   [ProductoController::class, 'edit'])        ->name('inventario.edit');
    Route::put('/inventario/{inventario}',          [ProductoController::class, 'update'])      ->name('inventario.update');
    Route::delete('/inventario/{inventario}',       [ProductoController::class, 'destroy'])     ->name('inventario.destroy');
    Route::post('/inventario/{inventario}/ajustar', [ProductoController::class, 'ajustarStock'])->name('inventario.ajustar');

    // ── Facturación ───────────────────────────────────────────
    Route::get('/facturas',                    [FacturaController::class, 'index'])       ->name('facturas.index');
    Route::get('/facturas/crear',              [FacturaController::class, 'create'])      ->name('facturas.create');
    Route::post('/facturas',                   [FacturaController::class, 'store'])       ->name('facturas.store');
    Route::get('/facturas/{factura}',          [FacturaController::class, 'show'])        ->name('facturas.show');
    Route::get('/facturas/{factura}/editar',   [FacturaController::class, 'edit'])        ->name('facturas.edit');
    Route::put('/facturas/{factura}',          [FacturaController::class, 'update'])      ->name('facturas.update');
    Route::delete('/facturas/{factura}',       [FacturaController::class, 'destroy'])     ->name('facturas.destroy');
    Route::patch('/facturas/{factura}/estado', [FacturaController::class, 'cambiarEstado'])->name('facturas.estado');
    Route::get('/facturas/{factura}/pdf',      [FacturaController::class, 'pdf'])         ->name('facturas.pdf');
    Route::get('/facturas/{factura}/enviar',   [FacturaController::class, 'formEnviar'])  ->name('facturas.formEnviar');
    Route::post('/facturas/{factura}/enviar',  [FacturaController::class, 'enviar'])      ->name('facturas.enviar');

    // ── Notas de Crédito ──────────────────────────────────────
    Route::get('/notas-credito',             [NotaCreditoController::class, 'index'])  ->name('notas_credito.index');
    Route::get('/notas-credito/crear',       [NotaCreditoController::class, 'create']) ->name('notas_credito.create');
    Route::post('/notas-credito',            [NotaCreditoController::class, 'store'])  ->name('notas_credito.store');
    Route::get('/notas-credito/{nota}',      [NotaCreditoController::class, 'show'])   ->name('notas_credito.show');
    Route::get('/notas-credito/{nota}/pdf',  [NotaCreditoController::class, 'pdf'])    ->name('notas_credito.pdf');

    // ── Cotizaciones ──────────────────────────────────────────
    Route::get('/cotizaciones',                        [CotizacionController::class, 'index'])        ->name('cotizaciones.index');
    Route::get('/cotizaciones/crear',                  [CotizacionController::class, 'create'])       ->name('cotizaciones.create');
    Route::post('/cotizaciones',                       [CotizacionController::class, 'store'])        ->name('cotizaciones.store');
    Route::get('/cotizaciones/{cotizacion}',           [CotizacionController::class, 'show'])         ->name('cotizaciones.show');
    Route::delete('/cotizaciones/{cotizacion}',        [CotizacionController::class, 'destroy'])      ->name('cotizaciones.destroy');
    Route::patch('/cotizaciones/{cotizacion}/estado',  [CotizacionController::class, 'cambiarEstado'])->name('cotizaciones.estado');
    Route::post('/cotizaciones/{cotizacion}/convertir',[CotizacionController::class, 'convertir'])    ->name('cotizaciones.convertir');
    Route::get('/cotizaciones/{cotizacion}/pdf',       [CotizacionController::class, 'pdf'])          ->name('cotizaciones.pdf');

    // ── Órdenes de Compra ─────────────────────────────────────
    Route::get('/ordenes',                      [OrdenCompraController::class, 'index'])        ->name('ordenes.index');
    Route::get('/ordenes/crear',                [OrdenCompraController::class, 'create'])       ->name('ordenes.create');
    Route::post('/ordenes',                     [OrdenCompraController::class, 'store'])        ->name('ordenes.store');
    Route::get('/ordenes/{orden}',              [OrdenCompraController::class, 'show'])         ->name('ordenes.show');
    Route::get('/ordenes/{orden}/editar',       [OrdenCompraController::class, 'edit'])         ->name('ordenes.edit');
    Route::put('/ordenes/{orden}',              [OrdenCompraController::class, 'update'])       ->name('ordenes.update');
    Route::delete('/ordenes/{orden}',           [OrdenCompraController::class, 'destroy'])      ->name('ordenes.destroy');
    Route::patch('/ordenes/{orden}/estado',     [OrdenCompraController::class, 'cambiarEstado'])->name('ordenes.estado');
    Route::post('/ordenes/{orden}/recibir',     [OrdenCompraController::class, 'recibir'])      ->name('ordenes.recibir');
    Route::get('/ordenes/{orden}/pdf',          [OrdenCompraController::class, 'pdf'])          ->name('ordenes.pdf');

    // ── Recibos de Caja ───────────────────────────────────────
    Route::get('/recibos',              [ReciboCajaController::class, 'index'])  ->name('recibos.index');
    Route::get('/recibos/crear',        [ReciboCajaController::class, 'create']) ->name('recibos.create');
    Route::post('/recibos',             [ReciboCajaController::class, 'store'])  ->name('recibos.store');
    Route::get('/recibos/{recibo}',     [ReciboCajaController::class, 'show'])   ->name('recibos.show');
    Route::delete('/recibos/{recibo}',  [ReciboCajaController::class, 'destroy'])->name('recibos.destroy');
    Route::get('/recibos/{recibo}/pdf', [ReciboCajaController::class, 'pdf'])    ->name('recibos.pdf');

    // ── Reportes ──────────────────────────────────────────────
    Route::get('/reportes',                [ReporteController::class, 'index'])        ->name('reportes.index');
    Route::get('/reportes/ventas',         [ReporteController::class, 'ventas'])       ->name('reportes.ventas');
    Route::get('/reportes/inventario',     [ReporteController::class, 'inventario'])   ->name('reportes.inventario');
    Route::get('/reportes/cartera',        [ReporteController::class, 'cartera'])      ->name('reportes.cartera');
    Route::get('/reportes/ventas/pdf',     [ReporteController::class, 'ventasPdf'])    ->name('reportes.ventas.pdf');
    Route::get('/reportes/inventario/pdf', [ReporteController::class, 'inventarioPdf'])->name('reportes.inventario.pdf');
    Route::get('/reportes/cartera/pdf',    [ReporteController::class, 'carteraPdf'])   ->name('reportes.cartera.pdf');

    // ── Empresa ───────────────────────────────────────────────
    Route::get('/empresa',              [EmpresaController::class, 'index'])     ->name('empresa.index');
    Route::put('/empresa',              [EmpresaController::class, 'update'])    ->name('empresa.update');
    Route::delete('/empresa/logo',      [EmpresaController::class, 'deleteLogo'])->name('empresa.logo.delete');
    Route::post('/empresa/probar-mail', [EmpresaController::class, 'probarMail'])->name('empresa.probarMail');

    // ── Usuarios ──────────────────────────────────────────────
    Route::get('/usuarios',                    [UsuarioController::class, 'index'])       ->name('usuarios.index');
    Route::get('/usuarios/crear',              [UsuarioController::class, 'create'])      ->name('usuarios.create');
    Route::post('/usuarios',                   [UsuarioController::class, 'store'])       ->name('usuarios.store');
    Route::get('/usuarios/{usuario}/editar',   [UsuarioController::class, 'edit'])        ->name('usuarios.edit');
    Route::put('/usuarios/{usuario}',          [UsuarioController::class, 'update'])      ->name('usuarios.update');
    Route::delete('/usuarios/{usuario}',       [UsuarioController::class, 'destroy'])     ->name('usuarios.destroy');
    Route::patch('/usuarios/{usuario}/activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.activo');

    // ── Categorías (solo admin) ───────────────────────────────
    Route::get('/categorias',                    [CategoriaController::class, 'index'])  ->name('categorias.index')  ->middleware('can:ver usuarios');
    Route::get('/categorias/crear',              [CategoriaController::class, 'create']) ->name('categorias.create') ->middleware('can:ver usuarios');
    Route::post('/categorias',                   [CategoriaController::class, 'store'])  ->name('categorias.store')  ->middleware('can:ver usuarios');
    Route::get('/categorias/{categoria}/editar', [CategoriaController::class, 'edit'])   ->name('categorias.edit')   ->middleware('can:ver usuarios');
    Route::put('/categorias/{categoria}',        [CategoriaController::class, 'update']) ->name('categorias.update') ->middleware('can:ver usuarios');
    Route::delete('/categorias/{categoria}',     [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('can:ver usuarios');

    // ── Unidades de Medida (solo admin) ───────────────────────
    Route::get('/unidades',                  [UnidadMedidaController::class, 'index'])  ->name('unidades.index')  ->middleware('can:ver usuarios');
    Route::get('/unidades/crear',            [UnidadMedidaController::class, 'create']) ->name('unidades.create') ->middleware('can:ver usuarios');
    Route::post('/unidades',                 [UnidadMedidaController::class, 'store'])  ->name('unidades.store')  ->middleware('can:ver usuarios');
    Route::get('/unidades/{unidad}/editar',  [UnidadMedidaController::class, 'edit'])   ->name('unidades.edit')   ->middleware('can:ver usuarios');
    Route::put('/unidades/{unidad}',         [UnidadMedidaController::class, 'update']) ->name('unidades.update') ->middleware('can:ver usuarios');
    Route::delete('/unidades/{unidad}',      [UnidadMedidaController::class, 'destroy'])->name('unidades.destroy')->middleware('can:ver usuarios');

    // ── Sesiones activas (solo admin) ─────────────────────────
    Route::get('/sesiones',         [SesionController::class, 'index'])     ->name('sesiones.index')      ->middleware('can:ver usuarios');
    Route::delete('/sesiones/{id}', [SesionController::class, 'destroy'])   ->name('sesiones.destroy')    ->middleware('can:ver usuarios');
    Route::delete('/sesiones',      [SesionController::class, 'destroyAll'])->name('sesiones.destroyAll') ->middleware('can:ver usuarios');

    // ── Auditoría (solo admin) ────────────────────────────────
    Route::get('/auditoria', [AuditoriaController::class, 'index'])
        ->name('auditoria.index')
        ->middleware('can:ver usuarios');

    // ── Backup (solo admin) ───────────────────────────────────
    Route::get('/backup',      [BackupController::class, 'index'])         ->name('backup.index') ->middleware('can:ver usuarios');
    Route::get('/backup/json', [BackupController::class, 'descargarJson']) ->name('backup.json')  ->middleware('can:ver usuarios');
    Route::post('/backup/csv', [BackupController::class, 'descargarCsv'])  ->name('backup.csv')   ->middleware('can:ver usuarios');
    Route::get('/backup/sql',  [BackupController::class, 'descargarSql'])  ->name('backup.sql')   ->middleware('can:ver usuarios');

    // ── APIs internas ─────────────────────────────────────────

    Route::get('/api/proveedores/buscar', function(\Illuminate\Http\Request $req) {
        $proveedores = \App\Models\Proveedor::where('activo', true)
            ->where(function($q) use ($req) {
                $q->where('razon_social',    'like', '%'.$req->q.'%')
                  ->orWhere('numero_documento','like', '%'.$req->q.'%');
            })
            ->limit(10)
            ->get(['id','razon_social','tipo_documento','numero_documento',
                   'digito_verificacion','plazo_pago','retefuente_pct']);
        return response()->json($proveedores);
    })->middleware('auth');

    Route::get('/api/productos/buscar', function(\Illuminate\Http\Request $req) {
        $productos = \App\Models\Producto::where('activo', true)
            ->where(function($q) use ($req) {
                $q->where('nombre', 'like', '%'.$req->q.'%')
                  ->orWhere('codigo', 'like', '%'.$req->q.'%');
            })
            ->limit(10)
            ->get(['id','codigo','nombre','precio_venta','precio_venta2','precio_venta3','iva_pct','unidad_medida_id']);

        $lista = $req->lista_precio ?? 'general';

        $productos = $productos->map(function($p) use ($lista) {
            $p->precio_aplicado = match($lista) {
                'mayorista' => $p->precio_venta2 > 0 ? $p->precio_venta2 : $p->precio_venta,
                'especial'  => $p->precio_venta3 > 0 ? $p->precio_venta3 : $p->precio_venta,
                default     => $p->precio_venta,
            };
            return $p;
        });

        return response()->json($productos);
    })->middleware('auth');

    Route::get('/api/facturas/buscar', function(\Illuminate\Http\Request $req) {
        $facturas = \App\Models\Factura::whereIn('estado', ['emitida', 'vencida'])
            ->where(function($q) use ($req) {
                $q->where('numero',        'like', '%'.$req->q.'%')
                  ->orWhere('cliente_nombre','like', '%'.$req->q.'%');
            })
            ->limit(10)
            ->get(['id','numero','cliente_nombre','total','total_pagado'])
            ->map(function($f) {
                $f->saldo = max(0, $f->total - $f->total_pagado);
                return $f;
            });
        return response()->json($facturas);
    })->middleware('auth');

    Route::get('/api/clientes/buscar', function(\Illuminate\Http\Request $req) {
        $clientes = \App\Models\Cliente::where('activo', true)
            ->where(function($q) use ($req) {
                $q->where('nombres',           'like', '%'.$req->q.'%')
                  ->orWhere('apellidos',        'like', '%'.$req->q.'%')
                  ->orWhere('razon_social',     'like', '%'.$req->q.'%')
                  ->orWhere('numero_documento', 'like', '%'.$req->q.'%');
            })
            ->limit(10)
            ->get(['id','nombres','apellidos','razon_social','numero_documento',
                   'tipo_documento','retefuente_pct','reteiva_pct','reteica_pct',
                   'plazo_pago','email','direccion','lista_precio']); // ← lista_precio agregado
        return response()->json($clientes);
    })->middleware('auth');

});

require __DIR__.'/auth.php';