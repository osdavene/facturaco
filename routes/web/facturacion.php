<?php

use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DianController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\ImpuestosController;
use App\Http\Controllers\NotaCreditoController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReciboCajaController;
use App\Http\Controllers\RemisionController;
use App\Http\Controllers\WompiController;
use Illuminate\Support\Facades\Route;

// ── POS ───────────────────────────────────────────────────────────────────
Route::middleware(['modulo:facturacion', 'can:crear facturas'])->group(function () {
    Route::get('/pos',                    [PosController::class, 'index'])  ->name('pos.index');
    Route::post('/pos',                   [PosController::class, 'store'])  ->name('pos.store');
    Route::get('/pos/ticket/{factura}',   [PosController::class, 'ticket']) ->name('pos.ticket');
});

// ── Facturas ──────────────────────────────────────────────────────────────
Route::middleware('modulo:facturacion')->group(function () {
    Route::get('/facturas',                    [FacturaController::class, 'index'])        ->name('facturas.index')       ->middleware('can:ver facturas');
    Route::get('/facturas/crear',              [FacturaController::class, 'create'])       ->name('facturas.create')      ->middleware('can:crear facturas');
    Route::post('/facturas',                   [FacturaController::class, 'store'])        ->name('facturas.store')       ->middleware('can:crear facturas');
    Route::get('/facturas/{factura}',          [FacturaController::class, 'show'])         ->name('facturas.show')        ->middleware('can:ver facturas');
    Route::get('/facturas/{factura}/editar',   [FacturaController::class, 'edit'])         ->name('facturas.edit')        ->middleware('can:editar facturas');
    Route::put('/facturas/{factura}',          [FacturaController::class, 'update'])       ->name('facturas.update')      ->middleware('can:editar facturas');
    Route::delete('/facturas/{factura}',       [FacturaController::class, 'destroy'])      ->name('facturas.destroy')     ->middleware('can:anular facturas');
    Route::patch('/facturas/{factura}/estado', [FacturaController::class, 'cambiarEstado'])->name('facturas.estado')      ->middleware('can:anular facturas');
    Route::get('/facturas/{factura}/pdf',      [FacturaController::class, 'pdf'])          ->name('facturas.pdf')         ->middleware('can:ver facturas');
    Route::get('/facturas/{factura}/enviar',   [FacturaController::class, 'formEnviar'])   ->name('facturas.formEnviar')  ->middleware('can:ver facturas');
    Route::post('/facturas/{factura}/enviar',  [FacturaController::class, 'enviar'])       ->name('facturas.enviar')      ->middleware('can:ver facturas');
    Route::delete('/facturas',                          [FacturaController::class, 'bulkDelete'])       ->name('facturas.bulk-delete')  ->middleware('can:anular facturas');
    Route::post('/facturas/{factura}/dian/enviar',      [DianController::class, 'enviar'])          ->name('facturas.dian.enviar')  ->middleware('can:crear facturas');
    Route::get('/facturas/{factura}/dian/estado',       [DianController::class, 'consultarEstado']) ->name('facturas.dian.estado')  ->middleware('can:ver facturas');
    Route::get('/facturas/{factura}/dian/xml',          [DianController::class, 'xml'])             ->name('facturas.dian.xml')     ->middleware('can:ver facturas');

    // Wompi retorno (dentro de módulo facturacion)
    Route::get('/facturas/{factura}/wompi/retorno', [WompiController::class, 'retorno'])->name('wompi.retorno');

    // ── Notas de Crédito ──────────────────────────────────────────────────
    Route::get('/notas-credito',            [NotaCreditoController::class, 'index'])  ->name('notas_credito.index')  ->middleware('can:ver facturas');
    Route::get('/notas-credito/crear',      [NotaCreditoController::class, 'create']) ->name('notas_credito.create') ->middleware('can:anular facturas');
    Route::post('/notas-credito',           [NotaCreditoController::class, 'store'])  ->name('notas_credito.store')  ->middleware('can:anular facturas');
    Route::get('/notas-credito/{nota}',     [NotaCreditoController::class, 'show'])   ->name('notas_credito.show')   ->middleware('can:ver facturas');
    Route::get('/notas-credito/{nota}/pdf', [NotaCreditoController::class, 'pdf'])    ->name('notas_credito.pdf')    ->middleware('can:ver facturas');

    // ── Cotizaciones ──────────────────────────────────────────────────────
    Route::get('/cotizaciones',                         [CotizacionController::class, 'index'])        ->name('cotizaciones.index')       ->middleware('can:ver cotizaciones');
    Route::get('/cotizaciones/crear',                   [CotizacionController::class, 'create'])       ->name('cotizaciones.create')      ->middleware('can:crear cotizaciones');
    Route::post('/cotizaciones',                        [CotizacionController::class, 'store'])        ->name('cotizaciones.store')       ->middleware('can:crear cotizaciones');
    Route::get('/cotizaciones/{cotizacion}',            [CotizacionController::class, 'show'])         ->name('cotizaciones.show')        ->middleware('can:ver cotizaciones');
    Route::delete('/cotizaciones/{cotizacion}',         [CotizacionController::class, 'destroy'])      ->name('cotizaciones.destroy')     ->middleware('can:editar cotizaciones');
    Route::patch('/cotizaciones/{cotizacion}/estado',   [CotizacionController::class, 'cambiarEstado'])->name('cotizaciones.estado')      ->middleware('can:editar cotizaciones');
    Route::post('/cotizaciones/{cotizacion}/convertir', [CotizacionController::class, 'convertir'])    ->name('cotizaciones.convertir')   ->middleware('can:crear facturas');
    Route::get('/cotizaciones/{cotizacion}/pdf',        [CotizacionController::class, 'pdf'])          ->name('cotizaciones.pdf')         ->middleware('can:ver cotizaciones');
    Route::delete('/cotizaciones',                      [CotizacionController::class, 'bulkDelete'])   ->name('cotizaciones.bulk-delete') ->middleware('can:editar cotizaciones');

    // ── Remisiones ────────────────────────────────────────────────────────
    Route::get('/remisiones',                       [RemisionController::class, 'index'])        ->name('remisiones.index')    ->middleware('can:ver facturas');
    Route::get('/remisiones/crear',                 [RemisionController::class, 'create'])       ->name('remisiones.create')   ->middleware('can:crear facturas');
    Route::post('/remisiones',                      [RemisionController::class, 'store'])        ->name('remisiones.store')    ->middleware('can:crear facturas');
    Route::get('/remisiones/{remision}',            [RemisionController::class, 'show'])         ->name('remisiones.show')     ->middleware('can:ver facturas');
    Route::delete('/remisiones/{remision}',         [RemisionController::class, 'destroy'])      ->name('remisiones.destroy')  ->middleware('can:anular facturas');
    Route::patch('/remisiones/{remision}/estado',   [RemisionController::class, 'cambiarEstado'])->name('remisiones.estado')   ->middleware('can:editar facturas');
    Route::post('/remisiones/{remision}/convertir', [RemisionController::class, 'convertir'])    ->name('remisiones.convertir')->middleware('can:crear facturas');
    Route::get('/remisiones/{remision}/pdf',        [RemisionController::class, 'pdf'])          ->name('remisiones.pdf')      ->middleware('can:ver facturas');

    // ── Recibos de Caja ───────────────────────────────────────────────────
    Route::get('/recibos',              [ReciboCajaController::class, 'index'])  ->name('recibos.index')   ->middleware('can:ver facturas');
    Route::get('/recibos/crear',        [ReciboCajaController::class, 'create']) ->name('recibos.create')  ->middleware('can:crear facturas');
    Route::post('/recibos',             [ReciboCajaController::class, 'store'])  ->name('recibos.store')   ->middleware('can:crear facturas');
    Route::get('/recibos/{recibo}',     [ReciboCajaController::class, 'show'])   ->name('recibos.show')    ->middleware('can:ver facturas');
    Route::delete('/recibos/{recibo}',  [ReciboCajaController::class, 'destroy'])->name('recibos.destroy') ->middleware('can:anular facturas');
    Route::get('/recibos/{recibo}/pdf', [ReciboCajaController::class, 'pdf'])    ->name('recibos.pdf')     ->middleware('can:ver facturas');

    // ── Impuestos ─────────────────────────────────────────────────────────
    Route::get('/impuestos',       [ImpuestosController::class, 'index'])->name('impuestos.index') ->middleware('can:ver configuracion');
    Route::get('/impuestos/pdf',   [ImpuestosController::class, 'pdf'])  ->name('impuestos.pdf')   ->middleware('can:exportar reportes');
    Route::get('/impuestos/excel', [ImpuestosController::class, 'excel'])->name('impuestos.excel') ->middleware('can:exportar reportes');
});
