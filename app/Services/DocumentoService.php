<?php

namespace App\Services;

/**
 * Lógica de cálculo de totales para documentos de venta y compra.
 * Centraliza la aritmética de ítems para no duplicarla en cada controlador.
 */
class DocumentoService
{
    /**
     * Procesa un arreglo raw de ítems (del request) y devuelve
     * los ítems con sus valores calculados más los totales del documento.
     *
     * @param  array<int, array<string, mixed>> $rawItems
     * @return array{
     *   items: array<int, array<string, mixed>>,
     *   subtotal: float,
     *   descuento: float,
     *   iva: float,
     *   total: float,
     * }
     */
    public function calcularItems(array $rawItems): array
    {
        $items     = [];
        $subtotal  = 0.0;
        $descuento = 0.0;
        $iva       = 0.0;

        foreach ($rawItems as $i => $raw) {
            $cant    = (float) ($raw['cantidad']        ?? 0);
            $precio  = (float) ($raw['precio_unitario'] ?? 0);
            $descPct = (float) ($raw['descuento_pct']   ?? 0);
            $ivaPct  = (float) ($raw['iva_pct']         ?? 19);

            $bruto = $cant * $precio;
            $desc  = $bruto * ($descPct / 100);
            $base  = $bruto - $desc;
            $ivaL  = $base  * ($ivaPct  / 100);
            $total = $base  + $ivaL;

            $subtotal  += $base;
            $descuento += $desc;
            $iva       += $ivaL;

            $items[] = array_merge($raw, [
                'orden'           => $i,
                'descuento_pct'   => $descPct,
                'descuento'       => $desc,
                'iva_pct'         => $ivaPct,
                'subtotal'        => $base,
                'iva'             => $ivaL,
                'total'           => $total,
                'cantidad'        => $cant,
                'precio_unitario' => $precio,
            ]);
        }

        return [
            'items'     => $items,
            'subtotal'  => $subtotal,
            'descuento' => $descuento,
            'iva'       => $iva,
            'total'     => $subtotal + $iva,
        ];
    }

    /**
     * Calcula retenciones sobre los totales ya calculados.
     *
     * @return array{retefuente: float, reteiva: float, reteica: float, total_neto: float}
     */
    public function calcularRetenciones(
        float $subtotal,
        float $iva,
        float $retefuentePct,
        float $reteivaPct,
        float $reteicaPct,
    ): array {
        $retefuente = $subtotal * ($retefuentePct / 100);
        $reteiva    = $iva      * ($reteivaPct    / 100);
        $reteica    = $subtotal * ($reteicaPct    / 100);

        return [
            'retefuente' => $retefuente,
            'reteiva'    => $reteiva,
            'reteica'    => $reteica,
            'total_neto' => $subtotal + $iva - $retefuente - $reteiva - $reteica,
        ];
    }
}
