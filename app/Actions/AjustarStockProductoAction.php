<?php

namespace App\Actions;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class AjustarStockProductoAction
{
    public static function execute(Producto $producto, array $data): void
    {
        $data['tipo']     = $data['tipo'] ?? 'ajuste';
        $data['cantidad'] = (float) $data['cantidad'];
        $data['motivo']   = $data['motivo'] ?? '';

        DB::transaction(function () use ($producto, $data) {
            $stockAnterior = $producto->stock_actual;

            if ($data['tipo'] === 'entrada') {
                $stockNuevo = $stockAnterior + $data['cantidad'];
            } elseif ($data['tipo'] === 'salida') {
                $stockNuevo = max(0, $stockAnterior - $data['cantidad']);
            } else {
                $stockNuevo = $data['cantidad']; // ajuste directo
            }

            $producto->update(['stock_actual' => $stockNuevo]);

            MovimientoInventario::create([
                'producto_id'    => $producto->id,
                'tipo'           => $data['tipo'],
                'cantidad'       => $data['cantidad'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo'    => $stockNuevo,
                'costo_unitario' => $producto->precio_compra,
                'motivo'         => $data['motivo'],
                'referencia'     => $data['referencia'] ?? '',
                'user_id'        => auth()->id(),
            ]);
        });
    }
}
