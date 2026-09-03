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
        Route::get('/',                     [AsientoContableController::class, 'index'])   ->name('index');
        Route::get('/crear',                [AsientoContableController::class, 'create'])  ->name('create')->middleware('can:crear recibos');
        Route::post('/',                    [AsientoContableController::class, 'store'])   ->name('store') ->middleware('can:crear recibos');
        Route::get('/exportar',             [AsientoContableController::class, 'exportar'])->name('exportar');
        Route::get('/{asiento}',            [AsientoContableController::class, 'show'])    ->name('show');
        Route::delete('/{asiento}/anular',  [AsientoContableController::class, 'anular'])  ->name('anular')->middleware('can:crear recibos');
    });

    // Reportes Contables
    Route::get('/balance-prueba',          [ReporteContableController::class, 'balancePrueba'])       ->name('reportes.balance-prueba');
    Route::get('/balance-prueba/exportar', [ReporteContableController::class, 'exportarBalancePrueba'])->name('reportes.balance-prueba.exportar');

    Route::get('/auxiliar',                [ReporteContableController::class, 'auxiliar'])            ->name('reportes.auxiliar');
    Route::get('/auxiliar/exportar',       [ReporteContableController::class, 'exportarAuxiliar'])     ->name('reportes.auxiliar.exportar');

    Route::get('/balance',                 [ReporteContableController::class, 'balance'])             ->name('reportes.balance');
    Route::get('/pyg',                     [ReporteContableController::class, 'pyg'])                 ->name('reportes.pyg');
});

