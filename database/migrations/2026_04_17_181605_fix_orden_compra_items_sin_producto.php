<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Crear productos para ítems de órdenes de compra que quedaron sin producto_id
        $items = DB::table('orden_compra_items as oci')
            ->join('ordenes_compra as oc', 'oc.id', '=', 'oci.orden_compra_id')
            ->whereNull('oci.producto_id')
            ->where('oci.cantidad_recibida', '>', 0)
            ->select(
                'oci.id as item_id',
                'oci.descripcion',
                'oci.codigo',
                'oci.precio_unitario',
                'oci.cantidad_recibida',
                'oc.empresa_id',
            )
            ->get();

        foreach ($items as $item) {
            $codigo = ($item->codigo && $item->codigo !== 'SIN-COD')
                ? $item->codigo
                : 'OC-' . strtoupper(preg_replace('/[\s\/]+/', '-', trim($item->descripcion)));

            // Evitar código duplicado en la misma empresa
            $codigoFinal = $codigo;
            $sufijo      = 1;
            while (DB::table('productos')->where('empresa_id', $item->empresa_id)->where('codigo', $codigoFinal)->exists()) {
                $codigoFinal = $codigo . '-' . $sufijo++;
            }

            $productoId = DB::table('productos')->insertGetId([
                'empresa_id'    => $item->empresa_id,
                'codigo'        => $codigoFinal,
                'nombre'        => $item->descripcion,
                'precio_compra' => $item->precio_unitario,
                'precio_venta'  => $item->precio_unitario,
                'stock_actual'  => $item->cantidad_recibida,
                'stock_minimo'  => 0,
                'es_servicio'   => false,
                'activo'        => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            DB::table('orden_compra_items')
                ->where('id', $item->item_id)
                ->update(['producto_id' => $productoId]);
        }
    }

    public function down(): void
    {
        // No reversible — no eliminamos productos creados en producción
    }
};
