<?php

use App\Http\Controllers\NominaController;
use App\Http\Controllers\NominaEmpleadoController;
use Illuminate\Support\Facades\Route;

Route::prefix('nomina')->name('nomina.')->middleware('modulo:nomina')->group(function () {

    // Gestión completa (crear antes de wildcard {nomina})
    Route::middleware('can:gestionar nomina')->group(function () {
        Route::get('/empleados/crear',                [NominaEmpleadoController::class, 'create'])      ->name('empleados.create');
        Route::post('/empleados',                     [NominaEmpleadoController::class, 'store'])       ->name('empleados.store');
        Route::get('/empleados/{empleado}/editar',    [NominaEmpleadoController::class, 'edit'])        ->name('empleados.edit')->whereNumber('empleado');
        Route::put('/empleados/{empleado}',           [NominaEmpleadoController::class, 'update'])      ->name('empleados.update')->whereNumber('empleado');
        Route::patch('/empleados/{empleado}/toggle',  [NominaEmpleadoController::class, 'toggleActivo'])->name('empleados.toggle')->whereNumber('empleado');
        Route::delete('/empleados/{empleado}',        [NominaEmpleadoController::class, 'destroy'])     ->name('empleados.destroy')->whereNumber('empleado');

        Route::get('/crear',                         [NominaController::class, 'create'])             ->name('create');
        Route::post('/',                             [NominaController::class, 'store'])              ->name('store');
        Route::delete('/{nomina}',                   [NominaController::class, 'destroy'])            ->name('destroy')->whereNumber('nomina');
        Route::post('/{nomina}/procesar',            [NominaController::class, 'procesar'])           ->name('procesar')->whereNumber('nomina');
        Route::post('/{nomina}/pagar',               [NominaController::class, 'marcarPagada'])       ->name('pagar')->whereNumber('nomina');
        Route::post('/{nomina}/anular',              [NominaController::class, 'anular'])             ->name('anular')->whereNumber('nomina');
        Route::patch('/{nomina}/liquidacion/{liquidacion}', [NominaController::class, 'actualizarLiquidacion'])->name('liquidacion.update')->whereNumber('nomina')->whereNumber('liquidacion');
        Route::post('/{nomina}/colilla/{liquidacion}/enviar', [NominaController::class, 'enviarColilla'])->name('colilla.enviar')->whereNumber('nomina')->whereNumber('liquidacion');
        Route::post('/{nomina}/colillas/enviar-todas', [NominaController::class, 'enviarColillasTodas'])->name('colillas.enviar-todas')->whereNumber('nomina');
    });

    // Solo lectura
    Route::middleware('can:ver nomina')->group(function () {
        Route::get('/empleados',                       [NominaEmpleadoController::class, 'index']) ->name('empleados.index');
        Route::get('/',                                [NominaController::class, 'index'])         ->name('index');
        Route::get('/{nomina}',                        [NominaController::class, 'show'])          ->name('show')->whereNumber('nomina');
        Route::get('/{nomina}/exportar-banco',         [NominaController::class, 'exportarBanco'])  ->name('exportar-banco')->whereNumber('nomina');
        Route::get('/{nomina}/colilla/{liquidacion}',  [NominaController::class, 'colilla'])       ->name('colilla')->whereNumber('nomina')->whereNumber('liquidacion');
    });
});
