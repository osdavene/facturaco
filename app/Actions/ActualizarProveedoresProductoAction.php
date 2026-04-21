<?php

namespace App\Actions;

use App\Models\Producto;

class ActualizarProveedoresProductoAction
{
    public static function execute(Producto $producto, array $proveedores): void
    {
        $syncData = [];
        foreach ($proveedores as $p) {
            $id = (int) ($p['id'] ?? 0);
            if (!$id) continue;
            $syncData[$id] = [
                'precio_compra_sugerido' => isset($p['precio_compra_sugerido']) && $p['precio_compra_sugerido'] !== ''
                    ? (float) $p['precio_compra_sugerido']
                    : null,
                'proveedor_principal' => !empty($p['proveedor_principal']),
            ];
        }
        $producto->proveedores()->sync($syncData);
    }
}
