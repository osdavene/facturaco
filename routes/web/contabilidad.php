<?php

use App\Http\Controllers\AsientoContableController;
use App\Http\Controllers\PlanCuentasController;
use App\Http\Controllers\ReporteContableController;
use Illuminate\Support\Facades\Route;

Route::prefix('contabilidad')->name('contabilidad.')->middleware('can:ver recibos')->group(function () {

    Route::prefix('plan-cuentas')->name('plan-cuentas.')->group(function () {
        Route::get('/',                    [PlanCuentasController::class, 'index'])  ->name('index');
        Route::get('/crear',               [PlanCuentasController::class, 'create']) ->name('create')->middleware('can:crear recibos');
        Route::post('/',                   [PlanCuentasController::class, 'store'])  ->name('store') ->middleware('can:crear recibos');
        Route::get('/{planCuenta}/editar', [PlanCuentasController::class, 'edit'])   ->name('edit')  ->middleware('can:crear recibos');
        Route::put('/{planCuenta}',        [PlanCuentasController::class, 'update']) ->name('update')->middleware('can:crear recibos');
    });

    Route::prefix('libro-diario')->name('libro-diario.')->group(function () {
        Route::get('/',          [AsientoContableController::class, 'index'])->name('index');
        Route::get('/{asiento}', [AsientoContableController::class, 'show']) ->name('show');
    });

    Route::get('/balance', [ReporteContableController::class, 'balance'])->name('reportes.balance');
    Route::get('/pyg',     [ReporteContableController::class, 'pyg'])    ->name('reportes.pyg');
});
