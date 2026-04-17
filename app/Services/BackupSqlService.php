<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BackupSqlService
{
    private const TABLAS = [
        'empresa', 'empresa_user', 'users',
        'clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida',
        'facturas', 'factura_items',
        'cotizaciones', 'cotizacion_items',
        'ordenes_compra', 'orden_compra_items',
        'recibos_caja', 'remisiones', 'remision_items',
        'movimientos_inventario', 'login_logs',
    ];

    public function generar(string $generadoPor): string
    {
        $sql  = "-- ================================================\n";
        $sql .= "-- BACKUP COMPLETO — FacturaCO (BackOffice)\n";
        $sql .= "-- Fecha:        " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "-- Generado por: {$generadoPor}\n";
        $sql .= "-- Base de datos: PostgreSQL\n";
        $sql .= "-- ADVERTENCIA: restaurar este archivo reemplaza todos los datos.\n";
        $sql .= "-- ================================================\n\n";
        $sql .= "SET client_encoding = 'UTF8';\n";
        $sql .= "SET standard_conforming_strings = on;\n\n";

        foreach (self::TABLAS as $tabla) {
            $sql .= $this->volcarTabla($tabla);
        }

        return $sql;
    }

    private function volcarTabla(string $tabla): string
    {
        try {
            $filas = DB::table($tabla)->get();

            $out  = "-- ────────────────────────────────────────\n";
            $out .= "-- Tabla: {$tabla} ({$filas->count()} registros)\n";
            $out .= "-- ────────────────────────────────────────\n";

            if ($filas->isEmpty()) {
                return $out . "-- (sin datos)\n\n";
            }

            foreach ($filas as $fila) {
                $cols    = array_keys((array) $fila);
                $colsSql = implode(', ', array_map(fn($c) => '"' . $c . '"', $cols));
                $vals    = array_map(function ($v) {
                    if (is_null($v))                return 'NULL';
                    if (is_bool($v))                return $v ? 'TRUE' : 'FALSE';
                    if (is_int($v) || is_float($v)) return $v;
                    return "'" . str_replace("'", "''", (string) $v) . "'";
                }, (array) $fila);

                $out .= "INSERT INTO \"{$tabla}\" ({$colsSql}) VALUES (" . implode(', ', $vals) . ");\n";
            }

            return $out . "\n";
        } catch (\Exception $e) {
            return "-- ERROR en {$tabla}: " . $e->getMessage() . "\n\n";
        }
    }
}
