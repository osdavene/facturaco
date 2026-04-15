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
use App\Http\Controllers\WompiController;
use App\Http\Controllers\EmpresaSelectorController;
use App\Http\Controllers\BackofficeController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\NominaEmpleadoController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

// ── Webhooks (sin CSRF, sin auth) ────────────────────────────
Route::post('/webhooks/wompi', [WompiController::class, 'webhook'])->name('wompi.webhook');

// ── Página de inicio ──────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('login');
});

// ── Rutas autenticadas ────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // ── Selección / creación de empresa (sin requerir empresa activa) ──
    Route::get('/seleccionar-empresa',       [EmpresaSelectorController::class, 'index'])  ->name('empresas.seleccionar');
    Route::post('/seleccionar-empresa/{id}', [EmpresaSelectorController::class, 'elegir']) ->name('empresas.elegir');
    Route::get('/empresas/crear',            [EmpresaSelectorController::class, 'crear'])  ->name('empresas.crear');
    Route::post('/empresas',                 [EmpresaSelectorController::class, 'store'])  ->name('empresas.store');

    // ── Perfil de usuario ─────────────────────────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])   ->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update']) ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── Perfil (PerfilController) ─────────────────────────────
    Route::get('/perfil',           [PerfilController::class, 'index'])         ->name('perfil.index');
    Route::put('/perfil',           [PerfilController::class, 'update'])        ->name('perfil.update');
    Route::put('/perfil/password',  [PerfilController::class, 'updatePassword'])->name('perfil.password');
    Route::post('/perfil/avatar',   [PerfilController::class, 'updateAvatar'])  ->name('perfil.avatar');
    Route::delete('/perfil/avatar', [PerfilController::class, 'deleteAvatar'])  ->name('perfil.avatar.delete');

    // ── Tema ──────────────────────────────────────────────────
    Route::post('/tema', function(Request $request) {
        $tema = $request->tema === 'light' ? 'light' : 'dark';
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update(['tema' => $tema]);
        return back();
    })->name('tema.cambiar');

    // ═══════════════════════════════════════════════════════════
    // Rutas que requieren empresa activa en sesión
    // ═══════════════════════════════════════════════════════════
    Route::middleware('empresa')->group(function () {

    // ── Dashboard ─────────────────────────────────────────────
    Route::get('/dashboard', function () {
        $empresa = \App\Models\Empresa::obtener();

        try {
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

            $facturasVencidas = \App\Models\Factura::where('estado', 'vencida')->count();
            $ultimasFacturas  = \App\Models\Factura::orderByDesc('created_at')->limit(6)->get();

            $ventasPorMes = collect();
            for ($i = 11; $i >= 0; $i--) {
                $fecha      = now()->subMonths($i);
                $fechaAnio  = now()->subMonths($i)->subYear();
                $total      = \App\Models\Factura::whereMonth('fecha_emision', $fecha->month)
                              ->whereYear('fecha_emision', $fecha->year)
                              ->where('estado', '!=', 'anulada')->sum('total');
                $totalAnio  = \App\Models\Factura::whereMonth('fecha_emision', $fechaAnio->month)
                              ->whereYear('fecha_emision', $fechaAnio->year)
                              ->where('estado', '!=', 'anulada')->sum('total');
                $ventasPorMes->push([
                    'mes'        => $fecha->locale('es')->isoFormat('MMM'),
                    'anio'       => $fecha->year,
                    'total'      => (float) $total,
                    'total_anio' => (float) $totalAnio,
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

            // ── Deltas para tendencias ────────────────────────────
            $mesAnterior       = now()->subMonth();
            $ventasMesAnterior = \App\Models\Factura::whereMonth('fecha_emision', $mesAnterior->month)
                                  ->whereYear('fecha_emision', $mesAnterior->year)
                                  ->where('estado', '!=', 'anulada')->sum('total');

            $ventasAyer        = \App\Models\Factura::whereDate('fecha_emision', today()->subDay())
                                  ->where('estado', '!=', 'anulada')->sum('total');

            $ventasAnoAnterior = \App\Models\Factura::whereYear('fecha_emision', now()->year - 1)
                                  ->where('estado', '!=', 'anulada')->sum('total');

            $deltaHoy = $ventasAyer > 0
                ? round((($ventasHoy - $ventasAyer) / $ventasAyer) * 100, 1)
                : ($ventasHoy > 0 ? 100 : 0);

            $deltaMes = $ventasMesAnterior > 0
                ? round((($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1)
                : ($ventasMes > 0 ? 100 : 0);

            $deltaAno = $ventasAnoAnterior > 0
                ? round((($ventasAno - $ventasAnoAnterior) / $ventasAnoAnterior) * 100, 1)
                : ($ventasAno > 0 ? 100 : 0);

            $ticketPromedio        = $facturasMes > 0 ? round($ventasMes / $facturasMes) : 0;
            $facturasMesAnterior   = \App\Models\Factura::whereMonth('fecha_emision', $mesAnterior->month)
                                      ->whereYear('fecha_emision', $mesAnterior->year)->count();
            $deltaTicket           = $facturasMesAnterior > 0
                ? round((($facturasMes - $facturasMesAnterior) / $facturasMesAnterior) * 100, 1)
                : ($facturasMes > 0 ? 100 : 0);

        } catch (\Throwable) {
            $ventasHoy = $ventasMes = $ventasAno = $cartera = $facturasMes = $facturasVencidas = 0;
            $ultimasFacturas = $ventasPorMes = $ventasSemana = $topClientes = $topProductos = collect();
            $ventasPorEstado = collect();
            $deltaHoy = $deltaMes = $deltaAno = $deltaTicket = 0;
            $ticketPromedio = 0;
        }

        try {
            $productosStockBajo = \App\Models\Producto::where('activo', true)
                                   ->where('es_servicio', false)
                                   ->whereColumn('stock_actual', '<=', 'stock_minimo')->count();
        } catch (\Throwable) {
            $productosStockBajo = 0;
        }

        try {
            $cotizacionesPend = \App\Models\Cotizacion::whereIn('estado', ['enviada', 'aceptada'])->count();
        } catch (\Throwable) {
            $cotizacionesPend = 0;
        }

        try {
            $ordenesPend = \App\Models\OrdenCompra::where('estado', 'aprobada')->count();
        } catch (\Throwable) {
            $ordenesPend = 0;
        }

        return view('dashboard', compact(
            'empresa', 'ventasHoy', 'ventasMes', 'ventasAno', 'cartera', 'facturasMes',
            'facturasVencidas', 'productosStockBajo', 'cotizacionesPend', 'ordenesPend',
            'ultimasFacturas', 'ventasSemana', 'ventasPorMes', 'topClientes',
            'topProductos', 'ventasPorEstado',
            'deltaHoy', 'deltaMes', 'deltaAno', 'deltaTicket', 'ticketPromedio'
        ));
    })->middleware('verified')->name('dashboard');

    // ── Clientes ──────────────────────────────────────────────
    Route::get('/clientes',                    [ClienteController::class, 'index'])  ->name('clientes.index')       ->middleware('can:ver clientes');
    Route::get('/clientes/crear',              [ClienteController::class, 'create']) ->name('clientes.create')      ->middleware('can:crear clientes');
    Route::post('/clientes',                   [ClienteController::class, 'store'])  ->name('clientes.store')       ->middleware('can:crear clientes');
    Route::get('/clientes/{cliente}',          [ClienteController::class, 'show'])   ->name('clientes.show')        ->middleware('can:ver clientes');
    Route::get('/clientes/{cliente}/editar',   [ClienteController::class, 'edit'])   ->name('clientes.edit')        ->middleware('can:editar clientes');
    Route::put('/clientes/{cliente}',          [ClienteController::class, 'update']) ->name('clientes.update')      ->middleware('can:editar clientes');
    Route::delete('/clientes/{cliente}',       [ClienteController::class, 'destroy'])     ->name('clientes.destroy')     ->middleware('can:eliminar clientes');
    Route::delete('/clientes',                 [ClienteController::class, 'bulkDelete'])  ->name('clientes.bulk-delete') ->middleware('can:eliminar clientes');

    // ── Proveedores ───────────────────────────────────────────
    Route::get('/proveedores',                    [ProveedorController::class, 'index'])  ->name('proveedores.index')       ->middleware('can:ver proveedores');
    Route::get('/proveedores/crear',              [ProveedorController::class, 'create']) ->name('proveedores.create')      ->middleware('can:crear proveedores');
    Route::post('/proveedores',                   [ProveedorController::class, 'store'])  ->name('proveedores.store')       ->middleware('can:crear proveedores');
    Route::get('/proveedores/{proveedor}',        [ProveedorController::class, 'show'])   ->name('proveedores.show')        ->middleware('can:ver proveedores');
    Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])   ->name('proveedores.edit')        ->middleware('can:editar proveedores');
    Route::put('/proveedores/{proveedor}',        [ProveedorController::class, 'update']) ->name('proveedores.update')      ->middleware('can:editar proveedores');
    Route::delete('/proveedores/{proveedor}',     [ProveedorController::class, 'destroy'])    ->name('proveedores.destroy')     ->middleware('can:editar proveedores');
    Route::delete('/proveedores',                 [ProveedorController::class, 'bulkDelete']) ->name('proveedores.bulk-delete') ->middleware('can:editar proveedores');

    // ── Inventario ────────────────────────────────────────────
    Route::middleware('modulo:inventario')->group(function () {
    Route::get('/inventario',                       [ProductoController::class, 'index'])       ->name('inventario.index')   ->middleware('can:ver inventario');
    Route::get('/inventario/crear',                 [ProductoController::class, 'create'])      ->name('inventario.create')  ->middleware('can:crear inventario');
    Route::post('/inventario',                      [ProductoController::class, 'store'])       ->name('inventario.store')   ->middleware('can:crear inventario');
    Route::get('/inventario/{inventario}',          [ProductoController::class, 'show'])        ->name('inventario.show')    ->middleware('can:ver inventario');
    Route::get('/inventario/{inventario}/editar',   [ProductoController::class, 'edit'])        ->name('inventario.edit')    ->middleware('can:editar inventario');
    Route::put('/inventario/{inventario}',          [ProductoController::class, 'update'])      ->name('inventario.update')  ->middleware('can:editar inventario');
    Route::delete('/inventario/{inventario}',       [ProductoController::class, 'destroy'])     ->name('inventario.destroy') ->middleware('can:editar inventario');
    Route::post('/inventario/{inventario}/ajustar', [ProductoController::class, 'ajustarStock'])->name('inventario.ajustar') ->middleware('can:editar inventario');
    Route::delete('/inventario',                    [ProductoController::class, 'bulkDelete'])  ->name('inventario.bulk-delete') ->middleware('can:editar inventario');
    });

    // ── POS ───────────────────────────────────────────────────
    Route::middleware(['modulo:facturacion', 'can:crear facturas'])->group(function () {
    Route::get('/pos',              [PosController::class, 'index']) ->name('pos.index');
    Route::post('/pos',             [PosController::class, 'store']) ->name('pos.store');
    Route::get('/pos/ticket/{factura}', [PosController::class, 'ticket'])->name('pos.ticket');
    });

    // ── Facturación ───────────────────────────────────────────
    Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/facturas',                    [FacturaController::class, 'index'])        ->name('facturas.index')       ->middleware('can:ver facturas');
    Route::get('/facturas/crear',              [FacturaController::class, 'create'])       ->name('facturas.create')      ->middleware('can:crear facturas');
    Route::post('/facturas',                   [FacturaController::class, 'store'])        ->name('facturas.store')       ->middleware('can:crear facturas');
    Route::get('/facturas/{factura}',          [FacturaController::class, 'show'])         ->name('facturas.show')        ->middleware('can:ver facturas');
    Route::get('/facturas/{factura}/editar',   [FacturaController::class, 'edit'])         ->name('facturas.edit')        ->middleware('can:editar facturas');
    Route::put('/facturas/{factura}',          [FacturaController::class, 'update'])       ->name('facturas.update')      ->middleware('can:editar facturas');
    Route::delete('/facturas/{factura}',       [FacturaController::class, 'destroy'])      ->name('facturas.destroy')     ->middleware('can:anular facturas');
    Route::patch('/facturas/{factura}/estado', [FacturaController::class, 'cambiarEstado'])->name('facturas.estado')      ->middleware('can:anular facturas');
    Route::get('/facturas/{factura}/pdf',      [FacturaController::class, 'pdf'])          ->name('facturas.pdf')         ->middleware('can:ver facturas');
    Route::get('/facturas/{factura}/enviar',   [FacturaController::class, 'formEnviar'])   ->name('facturas.formEnviar')  ->middleware('can:ver facturas');
    Route::post('/facturas/{factura}/enviar',  [FacturaController::class, 'enviar'])       ->name('facturas.enviar')      ->middleware('can:ver facturas');
    Route::delete('/facturas',                 [FacturaController::class, 'bulkDelete'])   ->name('facturas.bulk-delete') ->middleware('can:anular facturas');
    });

    // ── Wompi retorno ─────────────────────────────────────────
    Route::get('/facturas/{factura}/wompi/retorno', [WompiController::class, 'retorno'])->name('wompi.retorno');

    // ── Notas de Crédito ──────────────────────────────────────
    Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/notas-credito',             [NotaCreditoController::class, 'index'])  ->name('notas_credito.index')  ->middleware('can:ver facturas');
    Route::get('/notas-credito/crear',       [NotaCreditoController::class, 'create']) ->name('notas_credito.create') ->middleware('can:anular facturas');
    Route::post('/notas-credito',            [NotaCreditoController::class, 'store'])  ->name('notas_credito.store')  ->middleware('can:anular facturas');
    Route::get('/notas-credito/{nota}',      [NotaCreditoController::class, 'show'])   ->name('notas_credito.show')   ->middleware('can:ver facturas');
    Route::get('/notas-credito/{nota}/pdf',  [NotaCreditoController::class, 'pdf'])    ->name('notas_credito.pdf')    ->middleware('can:ver facturas');
    });

    // ── Cotizaciones ──────────────────────────────────────────
    Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/cotizaciones',                        [CotizacionController::class, 'index'])        ->name('cotizaciones.index')       ->middleware('can:ver cotizaciones');
    Route::get('/cotizaciones/crear',                  [CotizacionController::class, 'create'])       ->name('cotizaciones.create')      ->middleware('can:crear cotizaciones');
    Route::post('/cotizaciones',                       [CotizacionController::class, 'store'])        ->name('cotizaciones.store')       ->middleware('can:crear cotizaciones');
    Route::get('/cotizaciones/{cotizacion}',           [CotizacionController::class, 'show'])         ->name('cotizaciones.show')        ->middleware('can:ver cotizaciones');
    Route::delete('/cotizaciones/{cotizacion}',        [CotizacionController::class, 'destroy'])      ->name('cotizaciones.destroy')     ->middleware('can:editar cotizaciones');
    Route::patch('/cotizaciones/{cotizacion}/estado',  [CotizacionController::class, 'cambiarEstado'])->name('cotizaciones.estado')      ->middleware('can:editar cotizaciones');
    Route::post('/cotizaciones/{cotizacion}/convertir',[CotizacionController::class, 'convertir'])    ->name('cotizaciones.convertir')   ->middleware('can:crear facturas');
    Route::get('/cotizaciones/{cotizacion}/pdf',       [CotizacionController::class, 'pdf'])          ->name('cotizaciones.pdf')         ->middleware('can:ver cotizaciones');
    Route::delete('/cotizaciones',                     [CotizacionController::class, 'bulkDelete'])   ->name('cotizaciones.bulk-delete') ->middleware('can:editar cotizaciones');
    });

    // ── Órdenes de Compra ─────────────────────────────────────
    Route::middleware('modulo:inventario')->group(function () {
    Route::get('/ordenes',                      [OrdenCompraController::class, 'index'])        ->name('ordenes.index')        ->middleware('can:ver compras');
    Route::get('/ordenes/crear',                [OrdenCompraController::class, 'create'])       ->name('ordenes.create')       ->middleware('can:crear compras');
    Route::post('/ordenes',                     [OrdenCompraController::class, 'store'])        ->name('ordenes.store')        ->middleware('can:crear compras');
    Route::get('/ordenes/{orden}',              [OrdenCompraController::class, 'show'])         ->name('ordenes.show')         ->middleware('can:ver compras');
    Route::get('/ordenes/{orden}/editar',       [OrdenCompraController::class, 'edit'])         ->name('ordenes.edit')         ->middleware('can:crear compras');
    Route::put('/ordenes/{orden}',              [OrdenCompraController::class, 'update'])       ->name('ordenes.update')       ->middleware('can:crear compras');
    Route::delete('/ordenes/{orden}',           [OrdenCompraController::class, 'destroy'])      ->name('ordenes.destroy')      ->middleware('can:crear compras');
    Route::patch('/ordenes/{orden}/estado',     [OrdenCompraController::class, 'cambiarEstado'])->name('ordenes.estado')       ->middleware('can:aprobar compras');
    Route::post('/ordenes/{orden}/recibir',     [OrdenCompraController::class, 'recibir'])      ->name('ordenes.recibir')      ->middleware('can:crear compras');
    Route::get('/ordenes/{orden}/pdf',          [OrdenCompraController::class, 'pdf'])          ->name('ordenes.pdf')          ->middleware('can:ver compras');
    Route::delete('/ordenes',                   [OrdenCompraController::class, 'bulkDelete'])   ->name('ordenes.bulk-delete')  ->middleware('can:crear compras');
    });

    // ── Recibos de Caja ───────────────────────────────────────
    Route::middleware('modulo:contable')->group(function () {
    Route::get('/recibos',              [ReciboCajaController::class, 'index'])  ->name('recibos.index')   ->middleware('can:ver facturas');
    Route::get('/recibos/crear',        [ReciboCajaController::class, 'create']) ->name('recibos.create')  ->middleware('can:crear facturas');
    Route::post('/recibos',             [ReciboCajaController::class, 'store'])  ->name('recibos.store')   ->middleware('can:crear facturas');
    Route::get('/recibos/{recibo}',     [ReciboCajaController::class, 'show'])   ->name('recibos.show')    ->middleware('can:ver facturas');
    Route::delete('/recibos/{recibo}',  [ReciboCajaController::class, 'destroy'])->name('recibos.destroy') ->middleware('can:anular facturas');
    Route::get('/recibos/{recibo}/pdf', [ReciboCajaController::class, 'pdf'])    ->name('recibos.pdf')     ->middleware('can:ver facturas');
    });

    // ── Reportes ──────────────────────────────────────────────
    Route::get('/reportes',                [ReporteController::class, 'index'])        ->name('reportes.index')              ->middleware('can:ver reportes');
    Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/reportes/ventas',         [ReporteController::class, 'ventas'])       ->name('reportes.ventas')             ->middleware('can:ver reportes');
    Route::get('/reportes/cartera',        [ReporteController::class, 'cartera'])      ->name('reportes.cartera')            ->middleware('can:ver reportes');
    Route::get('/reportes/ventas/pdf',     [ReporteController::class, 'ventasPdf'])    ->name('reportes.ventas.pdf')         ->middleware('can:exportar reportes');
    Route::get('/reportes/cartera/pdf',    [ReporteController::class, 'carteraPdf'])   ->name('reportes.cartera.pdf')        ->middleware('can:exportar reportes');
    });
    Route::middleware('modulo:inventario')->group(function () {
    Route::get('/reportes/inventario',     [ReporteController::class, 'inventario'])   ->name('reportes.inventario')         ->middleware('can:ver reportes');
    Route::get('/reportes/inventario/pdf', [ReporteController::class, 'inventarioPdf'])->name('reportes.inventario.pdf')     ->middleware('can:exportar reportes');
    });

    // ── Empresa ───────────────────────────────────────────────
    Route::get('/empresa',              [EmpresaController::class, 'index'])     ->name('empresa.index')       ->middleware('can:ver configuracion');
    Route::put('/empresa',              [EmpresaController::class, 'update'])    ->name('empresa.update')      ->middleware('can:editar configuracion');
    Route::delete('/empresa/logo',      [EmpresaController::class, 'deleteLogo'])->name('empresa.logo.delete') ->middleware('can:editar configuracion');
    Route::post('/empresa/probar-mail', [EmpresaController::class, 'probarMail'])->name('empresa.probarMail')  ->middleware('can:editar configuracion');

    // ── Usuarios ──────────────────────────────────────────────
    Route::get('/usuarios',                    [UsuarioController::class, 'index'])       ->name('usuarios.index')  ->middleware('can:ver usuarios');
    Route::get('/usuarios/crear',              [UsuarioController::class, 'create'])      ->name('usuarios.create') ->middleware('can:crear usuarios');
    Route::post('/usuarios',                   [UsuarioController::class, 'store'])       ->name('usuarios.store')  ->middleware('can:crear usuarios');
    Route::get('/usuarios/{usuario}/editar',   [UsuarioController::class, 'edit'])        ->name('usuarios.edit')   ->middleware('can:editar usuarios');
    Route::put('/usuarios/{usuario}',          [UsuarioController::class, 'update'])      ->name('usuarios.update') ->middleware('can:editar usuarios');
    Route::delete('/usuarios/{usuario}',       [UsuarioController::class, 'destroy'])     ->name('usuarios.destroy')->middleware('can:eliminar usuarios');
    Route::patch('/usuarios/{usuario}/activo', [UsuarioController::class, 'toggleActivo'])->name('usuarios.activo') ->middleware('can:editar usuarios');

    // ── Categorías (requiere editar inventario) ───────────────
    Route::middleware('modulo:inventario')->group(function () {
    Route::get('/categorias',                    [CategoriaController::class, 'index'])  ->name('categorias.index')  ->middleware('can:editar inventario');
    Route::get('/categorias/crear',              [CategoriaController::class, 'create']) ->name('categorias.create') ->middleware('can:editar inventario');
    Route::post('/categorias',                   [CategoriaController::class, 'store'])  ->name('categorias.store')  ->middleware('can:editar inventario');
    Route::get('/categorias/{categoria}/editar', [CategoriaController::class, 'edit'])   ->name('categorias.edit')   ->middleware('can:editar inventario');
    Route::put('/categorias/{categoria}',        [CategoriaController::class, 'update']) ->name('categorias.update') ->middleware('can:editar inventario');
    Route::delete('/categorias/{categoria}',     [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('can:editar inventario');
    });

    // ── Unidades de Medida (requiere editar inventario) ───────
    Route::middleware('modulo:inventario')->group(function () {
    Route::get('/unidades',                  [UnidadMedidaController::class, 'index'])  ->name('unidades.index')  ->middleware('can:editar inventario');
    Route::get('/unidades/crear',            [UnidadMedidaController::class, 'create']) ->name('unidades.create') ->middleware('can:editar inventario');
    Route::post('/unidades',                 [UnidadMedidaController::class, 'store'])  ->name('unidades.store')  ->middleware('can:editar inventario');
    Route::get('/unidades/{unidad}/editar',  [UnidadMedidaController::class, 'edit'])   ->name('unidades.edit')   ->middleware('can:editar inventario');
    Route::put('/unidades/{unidad}',         [UnidadMedidaController::class, 'update']) ->name('unidades.update') ->middleware('can:editar inventario');
    Route::delete('/unidades/{unidad}',      [UnidadMedidaController::class, 'destroy'])->name('unidades.destroy')->middleware('can:editar inventario');
    });

    // ── Sesiones activas (solo admin) ─────────────────────────
    Route::get('/sesiones',         [SesionController::class, 'index'])     ->name('sesiones.index')      ->middleware('can:ver usuarios');
    Route::delete('/sesiones/{id}', [SesionController::class, 'destroy'])   ->name('sesiones.destroy')    ->middleware('can:ver usuarios');
    Route::delete('/sesiones',      [SesionController::class, 'destroyAll'])->name('sesiones.destroyAll') ->middleware('can:ver usuarios');

    // ── Auditoría (solo admin) ────────────────────────────────
    Route::get('/auditoria', [AuditoriaController::class, 'index'])
        ->name('auditoria.index')
        ->middleware('can:ver usuarios');

    // ── Backup de empresa (filtrado por empresa_grupo_ids) ───────────────
    Route::get('/backup',      [BackupController::class, 'index'])         ->name('backup.index') ->middleware('can:ver usuarios');
    Route::get('/backup/json', [BackupController::class, 'descargarJson']) ->name('backup.json')  ->middleware('can:ver usuarios');
    Route::post('/backup/csv', [BackupController::class, 'descargarCsv'])  ->name('backup.csv')   ->middleware('can:ver usuarios');

    // ── Remisiones ────────────────────────────────────────────
    Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/remisiones',                        [RemisionController::class, 'index'])        ->name('remisiones.index')    ->middleware('can:ver facturas');
    Route::get('/remisiones/crear',                  [RemisionController::class, 'create'])       ->name('remisiones.create')   ->middleware('can:crear facturas');
    Route::post('/remisiones',                       [RemisionController::class, 'store'])        ->name('remisiones.store')    ->middleware('can:crear facturas');
    Route::get('/remisiones/{remision}',             [RemisionController::class, 'show'])         ->name('remisiones.show')     ->middleware('can:ver facturas');
    Route::delete('/remisiones/{remision}',          [RemisionController::class, 'destroy'])      ->name('remisiones.destroy')  ->middleware('can:anular facturas');
    Route::patch('/remisiones/{remision}/estado',    [RemisionController::class, 'cambiarEstado'])->name('remisiones.estado')   ->middleware('can:editar facturas');
    Route::post('/remisiones/{remision}/convertir',  [RemisionController::class, 'convertir'])    ->name('remisiones.convertir')->middleware('can:crear facturas');
    Route::get('/remisiones/{remision}/pdf',         [RemisionController::class, 'pdf'])          ->name('remisiones.pdf')      ->middleware('can:ver facturas');
    });

    // ── Impuestos ─────────────────────────────────────────────
    Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/impuestos',         [ImpuestosController::class, 'index'])->name('impuestos.index') ->middleware('can:ver configuracion');
    Route::get('/impuestos/pdf',     [ImpuestosController::class, 'pdf'])  ->name('impuestos.pdf')   ->middleware('can:exportar reportes');
    Route::get('/impuestos/excel',   [ImpuestosController::class, 'excel'])->name('impuestos.excel') ->middleware('can:exportar reportes');
    });

    // ── Búsqueda ──────────────────────────────────────────────
    Route::get('/busqueda', [BusquedaController::class, 'buscar'])->name('busqueda');

    // ── Excel exports ─────────────────────────────────────────
    Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/reportes/ventas/excel',     [ReporteController::class, 'ventasExcel'])    ->name('reportes.ventas.excel')    ->middleware('can:exportar reportes');
    Route::get('/reportes/cartera/excel',    [ReporteController::class, 'carteraExcel'])   ->name('reportes.cartera.excel')   ->middleware('can:exportar reportes');
    });
    Route::middleware('modulo:inventario')->group(function () {
    Route::get('/reportes/inventario/excel', [ReporteController::class, 'inventarioExcel'])->name('reportes.inventario.excel')->middleware('can:exportar reportes');
    });

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

    Route::get('/api/clientes/buscar', function (\Illuminate\Http\Request $req) {
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

    // ── NÓMINA ────────────────────────────────────────────────────
    Route::prefix('nomina')->name('nomina.')->group(function () {
        // Empleados
        Route::get('/empleados',                    [NominaEmpleadoController::class, 'index'])       ->name('empleados.index');
        Route::get('/empleados/crear',              [NominaEmpleadoController::class, 'create'])      ->name('empleados.create');
        Route::post('/empleados',                   [NominaEmpleadoController::class, 'store'])       ->name('empleados.store');
        Route::get('/empleados/{empleado}/editar',  [NominaEmpleadoController::class, 'edit'])        ->name('empleados.edit');
        Route::put('/empleados/{empleado}',         [NominaEmpleadoController::class, 'update'])      ->name('empleados.update');
        Route::patch('/empleados/{empleado}/toggle',[NominaEmpleadoController::class, 'toggleActivo'])->name('empleados.toggle');
        Route::delete('/empleados/{empleado}',      [NominaEmpleadoController::class, 'destroy'])     ->name('empleados.destroy');

        // Períodos de nómina
        Route::get('/',                             [NominaController::class, 'index'])               ->name('index');
        Route::get('/crear',                        [NominaController::class, 'create'])              ->name('create');
        Route::post('/',                            [NominaController::class, 'store'])               ->name('store');
        Route::get('/{nomina}',                     [NominaController::class, 'show'])                ->name('show');
        Route::delete('/{nomina}',                  [NominaController::class, 'destroy'])             ->name('destroy');
        Route::post('/{nomina}/procesar',           [NominaController::class, 'procesar'])            ->name('procesar');
        Route::post('/{nomina}/pagar',              [NominaController::class, 'marcarPagada'])        ->name('pagar');
        Route::post('/{nomina}/anular',             [NominaController::class, 'anular'])              ->name('anular');

        // Liquidación individual
        Route::patch('/{nomina}/liquidacion/{liquidacion}', [NominaController::class, 'actualizarLiquidacion'])->name('liquidacion.update');

        // Colilla
        Route::get('/{nomina}/colilla/{liquidacion}', [NominaController::class, 'colilla'])           ->name('colilla');
    });

    }); // fin grupo middleware('empresa')

});

// ── BackOffice (solo superadmin de plataforma) ────────────────
Route::middleware(['auth', 'backoffice'])->prefix('backoffice')->name('backoffice.')->group(function () {
    Route::get('/',                                 [BackofficeController::class, 'dashboard'])     ->name('dashboard');
    Route::get('/empresas',                         [BackofficeController::class, 'empresasIndex']) ->name('empresas');
    Route::get('/empresas/crear',                   [BackofficeController::class, 'empresasCrear']) ->name('empresas.crear');
    Route::post('/empresas',                        [BackofficeController::class, 'empresasStore']) ->name('empresas.store');
    Route::get('/empresas/{empresa}/editar',        [BackofficeController::class, 'empresasEditar'])->name('empresas.editar');
    Route::get('/empresas/{empresa}/modulos',       [BackofficeController::class, 'modulos'])      ->name('empresas.modulos');
    Route::put('/empresas/{empresa}/modulos',       [BackofficeController::class, 'modulosUpdate'])->name('empresas.modulos.update');
    Route::put('/empresas/{empresa}',               [BackofficeController::class, 'empresasUpdate'])->name('empresas.update');
    Route::delete('/empresas/{empresa}',            [BackofficeController::class, 'empresasDestroy'])->name('empresas.destroy');
    Route::get('/empresas/{empresa}/crear-admin',   [BackofficeController::class, 'crearAdmin'])   ->name('empresas.admin.crear');
    Route::post('/empresas/{empresa}/crear-admin',  [BackofficeController::class, 'storeAdmin'])   ->name('empresas.admin.store');
    Route::post('/empresas/{empresa}/impersonar',   [BackofficeController::class, 'impersonar'])   ->name('impersonar');
    Route::post('/salir-impersonar',                [BackofficeController::class, 'salirImpersonar'])->name('salir');
    Route::get('/usuarios',                         [BackofficeController::class, 'usuariosIndex']) ->name('usuarios');
    Route::get('/usuarios/{usuario}/editar',        [BackofficeController::class, 'usuarioEditar']) ->name('usuarios.editar');
    Route::put('/usuarios/{usuario}',               [BackofficeController::class, 'usuarioUpdate']) ->name('usuarios.update');
    Route::delete('/usuarios/{usuario}',            [BackofficeController::class, 'usuarioDestroy'])->name('usuarios.destroy');
    Route::get('/backup',                           [BackofficeController::class, 'backupIndex'])    ->name('backup');
    Route::get('/backup/descargar',                 [BackofficeController::class, 'backupDescargar'])->name('backup.descargar');
});

require __DIR__.'/auth.php';