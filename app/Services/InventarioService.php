<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\Producto;

/**
 * Gestiona entradas y salidas de inventario con registro de movimiento.
 */
class InventarioService
{
    /**
     * Registra una salida de inventario (venta, despacho, etc.)
     */
    public function registrarSalida(
        Producto $producto,
        float    $cantidad,
        string   $referencia,
        int      $userId,
        string   $motivo = 'Venta',
    ): void {
        if ($producto->es_servicio) {
            return;
        }

        $stockAnterior = $producto->stock_actual;
        $stockNuevo    = $stockAnterior - $cantidad;

        $producto->update(['stock_actual' => $stockNuevo]);

        MovimientoInventario::create([
            'producto_id'    => $producto->id,
            'tipo'           => 'salida',
            'cantidad'       => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo'    => $stockNuevo,
            'motivo'         => $motivo,
            'referencia'     => $referencia,
            'user_id'        => $userId,
        ]);
    }

    /**
     * Registra una entrada de inventario (compra, devolución, ajuste, etc.)
     */
    public function registrarEntrada(
        Producto $producto,
        float    $cantidad,
        string   $referencia,
        int      $userId,
        string   $motivo      = 'Compra',
        float    $costoUnitario = 0.0,
    ): void {
        if ($producto->es_servicio) {
            return;
        }

        $stockAnterior = $producto->stock_actual;
        $stockNuevo    = $stockAnterior + $cantidad;

        $updates = ['stock_actual' => $stockNuevo];
        if ($costoUnitario > 0) {
            $updates['precio_compra'] = $costoUnitario;
        }

        $producto->update($updates);

        MovimientoInventario::create([
            'producto_id'    => $producto->id,
            'tipo'           => 'entrada',
            'cantidad'       => $cantidad,
            'stock_anterior' => $stockAnterior,
            'stock_nuevo'    => $stockNuevo,
            'costo_unitario' => $costoUnitario,
            'motivo'         => $motivo,
            'referencia'     => $referencia,
            'user_id'        => $userId,
        ]);
    }
}
