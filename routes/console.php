<?php
use Illuminate\Support\Facades\Schedule;
use App\Models\Factura;

// Marcar facturas vencidas automáticamente cada día
Schedule::call(function () {
    Factura::withoutGlobalScope('empresa')
           ->where('estado', 'emitida')
           ->where('fecha_vencimiento', '<', now()->startOfDay())
           ->update(['estado' => 'vencida']);
})->dailyAt('00:05')->name('marcar-facturas-vencidas');

// Enviar alertas por email cada día a las 8am
Schedule::command('alertas:enviar')
         ->dailyAt('08:00')
         ->name('enviar-alertas-diarias')
         ->withoutOverlapping();