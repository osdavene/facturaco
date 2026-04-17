<?php

use App\Http\Controllers\BackofficeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'backoffice'])->prefix('backoffice')->name('backoffice.')->group(function () {
    Route::get('/',                                [BackofficeController::class, 'dashboard'])      ->name('dashboard');
    Route::get('/empresas',                        [BackofficeController::class, 'empresasIndex'])  ->name('empresas');
    Route::get('/empresas/crear',                  [BackofficeController::class, 'empresasCrear'])  ->name('empresas.crear');
    Route::post('/empresas',                       [BackofficeController::class, 'empresasStore'])  ->name('empresas.store');
    Route::get('/empresas/{empresa}/editar',       [BackofficeController::class, 'empresasEditar']) ->name('empresas.editar');
    Route::get('/empresas/{empresa}/modulos',      [BackofficeController::class, 'modulos'])        ->name('empresas.modulos');
    Route::put('/empresas/{empresa}/modulos',      [BackofficeController::class, 'modulosUpdate'])  ->name('empresas.modulos.update');
    Route::put('/empresas/{empresa}',              [BackofficeController::class, 'empresasUpdate']) ->name('empresas.update');
    Route::delete('/empresas/{empresa}',           [BackofficeController::class, 'empresasDestroy'])->name('empresas.destroy');
    Route::get('/empresas/{empresa}/crear-admin',  [BackofficeController::class, 'crearAdmin'])     ->name('empresas.admin.crear');
    Route::post('/empresas/{empresa}/crear-admin', [BackofficeController::class, 'storeAdmin'])     ->name('empresas.admin.store');
    Route::post('/empresas/{empresa}/impersonar',  [BackofficeController::class, 'impersonar'])     ->name('impersonar');
    Route::post('/salir-impersonar',               [BackofficeController::class, 'salirImpersonar'])->name('salir');
    Route::get('/usuarios',                        [BackofficeController::class, 'usuariosIndex'])  ->name('usuarios');
    Route::get('/usuarios/{usuario}/editar',       [BackofficeController::class, 'usuarioEditar'])  ->name('usuarios.editar');
    Route::put('/usuarios/{usuario}',              [BackofficeController::class, 'usuarioUpdate'])  ->name('usuarios.update');
    Route::delete('/usuarios/{usuario}',           [BackofficeController::class, 'usuarioDestroy']) ->name('usuarios.destroy');
    Route::get('/backup',                          [BackofficeController::class, 'backupIndex'])     ->name('backup');
    Route::get('/backup/descargar',                [BackofficeController::class, 'backupDescargar']) ->name('backup.descargar');
});
