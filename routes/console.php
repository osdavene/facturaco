<?php
use Illuminate\Support\Facades\Schedule;
use App\Models\Factura;

// Marcar facturas vencidas automáticamente cada día
Schedule::call(function () {
    Factura::where('estado', 'emitida')
           ->where('fecha_vencimiento', '<', now()->startOfDay())
           ->update(['estado' => 'vencida']);
})->dailyAt('00:05')->name('marcar-facturas-vencidas');