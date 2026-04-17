<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\OrdenCompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UnidadMedidaController;
use Illuminate\Support\Facades\Route;

Route::middleware('modulo:inventario')->group(function () {

    // ── Productos / Inventario ────────────────────────────────────────────
    Route::get('/inventario',                         [ProductoController::class, 'index'])       ->name('inventario.index')       ->middleware('can:ver inventario');
    Route::get('/inventario/crear',                   [ProductoController::class, 'create'])      ->name('inventario.create')      ->middleware('can:crear inventario');
    Route::post('/inventario',                        [ProductoController::class, 'store'])       ->name('inventario.store')       ->middleware('can:crear inventario');
    Route::get('/inventario/{inventario}',            [ProductoController::class, 'show'])        ->name('inventario.show')        ->middleware('can:ver inventario');
    Route::get('/inventario/{inventario}/editar',     [ProductoController::class, 'edit'])        ->name('inventario.edit')        ->middleware('can:editar inventario');
    Route::put('/inventario/{inventario}',            [ProductoController::class, 'update'])      ->name('inventario.update')      ->middleware('can:editar inventario');
    Route::delete('/inventario/{inventario}',         [ProductoController::class, 'destroy'])     ->name('inventario.destroy')     ->middleware('can:editar inventario');
    Route::post('/inventario/{inventario}/ajustar',   [ProductoController::class, 'ajustarStock'])->name('inventario.ajustar')     ->middleware('can:editar inventario');
    Route::delete('/inventario',                      [ProductoController::class, 'bulkDelete'])  ->name('inventario.bulk-delete') ->middleware('can:editar inventario');

    // ── Órdenes de Compra ─────────────────────────────────────────────────
    Route::get('/ordenes',                    [OrdenCompraController::class, 'index'])        ->name('ordenes.index')       ->middleware('can:ver compras');
    Route::get('/ordenes/crear',              [OrdenCompraController::class, 'create'])       ->name('ordenes.create')      ->middleware('can:crear compras');
    Route::post('/ordenes',                   [OrdenCompraController::class, 'store'])        ->name('ordenes.store')       ->middleware('can:crear compras');
    Route::get('/ordenes/{orden}',            [OrdenCompraController::class, 'show'])         ->name('ordenes.show')        ->middleware('can:ver compras');
    Route::get('/ordenes/{orden}/editar',     [OrdenCompraController::class, 'edit'])         ->name('ordenes.edit')        ->middleware('can:crear compras');
    Route::put('/ordenes/{orden}',            [OrdenCompraController::class, 'update'])       ->name('ordenes.update')      ->middleware('can:crear compras');
    Route::delete('/ordenes/{orden}',         [OrdenCompraController::class, 'destroy'])      ->name('ordenes.destroy')     ->middleware('can:crear compras');
    Route::patch('/ordenes/{orden}/estado',   [OrdenCompraController::class, 'cambiarEstado'])->name('ordenes.estado')      ->middleware('can:aprobar compras');
    Route::post('/ordenes/{orden}/recibir',   [OrdenCompraController::class, 'recibir'])      ->name('ordenes.recibir')     ->middleware('can:crear compras');
    Route::get('/ordenes/{orden}/pdf',        [OrdenCompraController::class, 'pdf'])          ->name('ordenes.pdf')         ->middleware('can:ver compras');
    Route::delete('/ordenes',                 [OrdenCompraController::class, 'bulkDelete'])   ->name('ordenes.bulk-delete') ->middleware('can:crear compras');

    // ── Categorías ────────────────────────────────────────────────────────
    Route::get('/categorias',                    [CategoriaController::class, 'index'])  ->name('categorias.index')  ->middleware('can:editar inventario');
    Route::get('/categorias/crear',              [CategoriaController::class, 'create']) ->name('categorias.create') ->middleware('can:editar inventario');
    Route::post('/categorias',                   [CategoriaController::class, 'store'])  ->name('categorias.store')  ->middleware('can:editar inventario');
    Route::get('/categorias/{categoria}/editar', [CategoriaController::class, 'edit'])   ->name('categorias.edit')   ->middleware('can:editar inventario');
    Route::put('/categorias/{categoria}',        [CategoriaController::class, 'update']) ->name('categorias.update') ->middleware('can:editar inventario');
    Route::delete('/categorias/{categoria}',     [CategoriaController::class, 'destroy'])->name('categorias.destroy')->middleware('can:editar inventario');

    // ── Unidades de Medida ────────────────────────────────────────────────
    Route::get('/unidades',                 [UnidadMedidaController::class, 'index'])  ->name('unidades.index')  ->middleware('can:editar inventario');
    Route::get('/unidades/crear',           [UnidadMedidaController::class, 'create']) ->name('unidades.create') ->middleware('can:editar inventario');
    Route::post('/unidades',                [UnidadMedidaController::class, 'store'])  ->name('unidades.store')  ->middleware('can:editar inventario');
    Route::get('/unidades/{unidad}/editar', [UnidadMedidaController::class, 'edit'])   ->name('unidades.edit')   ->middleware('can:editar inventario');
    Route::put('/unidades/{unidad}',        [UnidadMedidaController::class, 'update']) ->name('unidades.update') ->middleware('can:editar inventario');
    Route::delete('/unidades/{unidad}',     [UnidadMedidaController::class, 'destroy'])->name('unidades.destroy')->middleware('can:editar inventario');
});
