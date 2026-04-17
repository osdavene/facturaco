<?php

use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;

Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index')->middleware('can:ver reportes');

Route::middleware(['modulo:facturacion', 'can:ver reportes'])->group(function () {
    Route::get('/reportes/ventas',      [ReporteController::class, 'ventas'])  ->name('reportes.ventas');
    Route::get('/reportes/cartera',     [ReporteController::class, 'cartera']) ->name('reportes.cartera');
});

Route::middleware(['modulo:facturacion', 'can:exportar reportes'])->group(function () {
    Route::get('/reportes/ventas/pdf',   [ReporteController::class, 'ventasPdf'])   ->name('reportes.ventas.pdf');
    Route::get('/reportes/ventas/excel', [ReporteController::class, 'ventasExcel']) ->name('reportes.ventas.excel');
    Route::get('/reportes/cartera/pdf',  [ReporteController::class, 'carteraPdf'])  ->name('reportes.cartera.pdf');
    Route::get('/reportes/cartera/excel',[ReporteController::class, 'carteraExcel'])->name('reportes.cartera.excel');
});

Route::middleware(['modulo:inventario', 'can:ver reportes'])->group(function () {
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
});

Route::middleware(['modulo:inventario', 'can:exportar reportes'])->group(function () {
    Route::get('/reportes/inventario/pdf',   [ReporteController::class, 'inventarioPdf'])  ->name('reportes.inventario.pdf');
    Route::get('/reportes/inventario/excel', [ReporteController::class, 'inventarioExcel'])->name('reportes.inventario.excel');
});
