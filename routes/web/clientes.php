<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProveedorController;
use Illuminate\Support\Facades\Route;

// ── Clientes ──────────────────────────────────────────────────────────────
Route::get('/clientes',                    [ClienteController::class, 'index'])     ->name('clientes.index')       ->middleware('can:ver clientes');
Route::get('/clientes/crear',              [ClienteController::class, 'create'])    ->name('clientes.create')      ->middleware('can:crear clientes');
Route::post('/clientes',                   [ClienteController::class, 'store'])     ->name('clientes.store')       ->middleware('can:crear clientes');
Route::get('/clientes/{cliente}',          [ClienteController::class, 'show'])      ->name('clientes.show')        ->middleware('can:ver clientes');
Route::get('/clientes/{cliente}/editar',   [ClienteController::class, 'edit'])      ->name('clientes.edit')        ->middleware('can:editar clientes');
Route::put('/clientes/{cliente}',          [ClienteController::class, 'update'])    ->name('clientes.update')      ->middleware('can:editar clientes');
Route::delete('/clientes/{cliente}',       [ClienteController::class, 'destroy'])   ->name('clientes.destroy')     ->middleware('can:eliminar clientes');
Route::delete('/clientes',                 [ClienteController::class, 'bulkDelete'])->name('clientes.bulk-delete') ->middleware('can:eliminar clientes');

// ── Proveedores ───────────────────────────────────────────────────────────
Route::get('/proveedores',                    [ProveedorController::class, 'index'])     ->name('proveedores.index')       ->middleware('can:ver proveedores');
Route::get('/proveedores/crear',              [ProveedorController::class, 'create'])    ->name('proveedores.create')      ->middleware('can:crear proveedores');
Route::post('/proveedores',                   [ProveedorController::class, 'store'])     ->name('proveedores.store')       ->middleware('can:crear proveedores');
Route::get('/proveedores/{proveedor}',        [ProveedorController::class, 'show'])      ->name('proveedores.show')        ->middleware('can:ver proveedores');
Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])      ->name('proveedores.edit')        ->middleware('can:editar proveedores');
Route::put('/proveedores/{proveedor}',        [ProveedorController::class, 'update'])    ->name('proveedores.update')      ->middleware('can:editar proveedores');
Route::delete('/proveedores/{proveedor}',     [ProveedorController::class, 'destroy'])   ->name('proveedores.destroy')     ->middleware('can:editar proveedores');
Route::delete('/proveedores',                 [ProveedorController::class, 'bulkDelete'])->name('proveedores.bulk-delete') ->middleware('can:editar proveedores');
