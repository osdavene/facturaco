<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoriaController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\CotizacionController;
use App\Http\Controllers\Api\V1\EmpresaController;
use App\Http\Controllers\Api\V1\FacturaController;
use App\Http\Controllers\Api\V1\NotaCreditoController;
use App\Http\Controllers\Api\V1\OrdenCompraController;
use App\Http\Controllers\Api\V1\ProductoController;
use App\Http\Controllers\Api\V1\ProveedorController;
use App\Http\Controllers\Api\V1\ReciboCajaController;
use App\Http\Controllers\Api\V1\RemisionController;
use App\Http\Controllers\Api\V1\UnidadMedidaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 — FacturaCO
|--------------------------------------------------------------------------
|
| Autenticación: Bearer token (Sanctum).
| Flujo: POST /auth/tokens  →  obtener token  →  usar en headers.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Autenticación (sin token requerido) ──────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login',  [AuthController::class, 'login'])->name('login');
        Route::post('tokens', [AuthController::class, 'crearToken'])->name('tokens.create');

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('tokens',           [AuthController::class, 'listarTokens'])->name('tokens.index');
            Route::delete('tokens/{id}',   [AuthController::class, 'revocarToken'])->name('tokens.destroy');
            Route::delete('logout',        [AuthController::class, 'logout'])->name('logout');
        });
    });

    // ── Recursos protegidos (token + empresa) ────────────────────────────
    Route::middleware(['auth:sanctum', 'api.empresa'])->group(function () {

        // Empresa actual
        Route::get('empresa',  [EmpresaController::class, 'show'])->name('empresa.show');
        Route::patch('empresa', [EmpresaController::class, 'update'])->name('empresa.update');

        // Catálogos
        Route::apiResource('clientes',       ClienteController::class);
        Route::apiResource('proveedores',    ProveedorController::class);
        Route::apiResource('productos',      ProductoController::class);
        Route::apiResource('categorias',     CategoriaController::class);
        Route::apiResource('unidades-medida', UnidadMedidaController::class)
             ->parameters(['unidades-medida' => 'unidadMedida']);

        // Documentos de venta
        Route::apiResource('facturas', FacturaController::class)->except(['create', 'edit']);
        Route::patch('facturas/{factura}/estado', [FacturaController::class, 'estado'])->name('facturas.estado');

        Route::apiResource('cotizaciones', CotizacionController::class)->except(['create', 'edit', 'update']);
        Route::patch('cotizaciones/{cotizacion}/estado', [CotizacionController::class, 'estado'])->name('cotizaciones.estado');

        Route::apiResource('remisiones', RemisionController::class)->except(['create', 'edit', 'store', 'update']);
        Route::patch('remisiones/{remision}/estado', [RemisionController::class, 'estado'])->name('remisiones.estado');

        // Documentos de compra
        Route::apiResource('ordenes-compra', OrdenCompraController::class)
             ->except(['create', 'edit', 'store', 'update'])
             ->parameters(['ordenes-compra' => 'ordenCompra']);
        Route::patch('ordenes-compra/{ordenCompra}/estado', [OrdenCompraController::class, 'estado'])->name('ordenes-compra.estado');

        // Pagos y notas
        Route::apiResource('recibos-caja', ReciboCajaController::class)
             ->except(['create', 'edit', 'store', 'update'])
             ->parameters(['recibos-caja' => 'reciboCaja']);

        Route::apiResource('notas-credito', NotaCreditoController::class)
             ->except(['create', 'edit', 'store', 'update'])
             ->parameters(['notas-credito' => 'notaCredito']);
    });
});
