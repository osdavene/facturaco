<?php

use App\Http\Controllers\EmpresaSelectorController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WompiController;

RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});

// ── Webhooks (sin CSRF, sin auth) ─────────────────────────────────────────
Route::post('/webhooks/wompi', [WompiController::class, 'webhook'])->name('wompi.webhook');

// ── Página de inicio ──────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Rutas autenticadas ────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // Sin empresa activa (selector / creación)
    Route::get('/seleccionar-empresa',       [EmpresaSelectorController::class, 'index'])  ->name('empresas.seleccionar');
    Route::post('/seleccionar-empresa/{id}', [EmpresaSelectorController::class, 'elegir']) ->name('empresas.elegir');
    Route::get('/empresas/crear',            [EmpresaSelectorController::class, 'crear'])  ->name('empresas.crear');
    Route::post('/empresas',                 [EmpresaSelectorController::class, 'store'])  ->name('empresas.store');

    require __DIR__.'/web/perfil.php';

    // ── Con empresa activa ────────────────────────────────────────────────
    Route::middleware('empresa')->group(function () {
        require __DIR__.'/web/dashboard.php';
        require __DIR__.'/web/clientes.php';
        require __DIR__.'/web/facturacion.php';
        require __DIR__.'/web/inventario.php';
        require __DIR__.'/web/reportes.php';
        require __DIR__.'/web/contabilidad.php';
        require __DIR__.'/web/nomina.php';
        require __DIR__.'/web/configuracion.php';
        require __DIR__.'/web/api_interna.php';
    });
});

require __DIR__.'/web/backoffice.php';
require __DIR__.'/auth.php';
