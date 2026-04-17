<?php

use App\Http\Controllers\NominaController;
use App\Http\Controllers\NominaEmpleadoController;
use Illuminate\Support\Facades\Route;

Route::prefix('nomina')->name('nomina.')->middleware('modulo:nomina')->group(function () {

    // Solo lectura
    Route::middleware('can:ver nomina')->group(function () {
        Route::get('/empleados',                       [NominaEmpleadoController::class, 'index']) ->name('empleados.index');
        Route::get('/',                                [NominaController::class, 'index'])         ->name('index');
        Route::get('/{nomina}',                        [NominaController::class, 'show'])          ->name('show');
        Route::get('/{nomina}/colilla/{liquidacion}',  [NominaController::class, 'colilla'])       ->name('colilla');
    });

    // Gestión completa
    Route::middleware('can:gestionar nomina')->group(function () {
        Route::get('/empleados/crear',                [NominaEmpleadoController::class, 'create'])      ->name('empleados.create');
        Route::post('/empleados',                     [NominaEmpleadoController::class, 'store'])       ->name('empleados.store');
        Route::get('/empleados/{empleado}/editar',    [NominaEmpleadoController::class, 'edit'])        ->name('empleados.edit');
        Route::put('/empleados/{empleado}',           [NominaEmpleadoController::class, 'update'])      ->name('empleados.update');
        Route::patch('/empleados/{empleado}/toggle',  [NominaEmpleadoController::class, 'toggleActivo'])->name('empleados.toggle');
        Route::delete('/empleados/{empleado}',        [NominaEmpleadoController::class, 'destroy'])     ->name('empleados.destroy');

        Route::get('/crear',                         [NominaController::class, 'create'])             ->name('create');
        Route::post('/',                             [NominaController::class, 'store'])              ->name('store');
        Route::delete('/{nomina}',                   [NominaController::class, 'destroy'])            ->name('destroy');
        Route::post('/{nomina}/procesar',            [NominaController::class, 'procesar'])           ->name('procesar');
        Route::post('/{nomina}/pagar',               [NominaController::class, 'marcarPagada'])       ->name('pagar');
        Route::post('/{nomina}/anular',              [NominaController::class, 'anular'])             ->name('anular');
        Route::patch('/{nomina}/liquidacion/{liquidacion}', [NominaController::class, 'actualizarLiquidacion'])->name('liquidacion.update');
    });
});
