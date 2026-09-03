<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupSqlService
{
    private const TABLAS = [
        'planes', 'empresa', 'users', 'empresa_user', 'modulos', 'empresa_modulo',
        'roles', 'permissions', 'model_has_roles', 'model_has_permissions', 'role_has_permissions',
        'clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida',
        'resoluciones_dian', 'facturas', 'factura_items', 'notas_credito', 'nota_credito_items',
        'cotizaciones', 'cotizacion_items',
        'ordenes_compra', 'orden_compra_items',
        'recibos_caja', 'remisiones', 'remision_items',
        'movimientos_inventario',
        'puc_cuentas', 'asientos_contables', 'asiento_contable_lineas',
        'empleados', 'nominas', 'nomina_empleado',
        'configuracion_plataforma', 'login_logs',
    ];

    public function generar(string $generadoPor): string
    {
        $sql  = "-- ========================================================\n";
        $sql .= "-- BACKUP COMPLETO INTEGRAL — FacCol (BackOffice)\n";
        $sql .= "-- Fecha:         " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "-- Generado por:  {$generadoPor}\n";
        $sql .= "-- Base de datos: PostgreSQL / MySQL Compatible\n";
        $sql .= "-- ========================================================\n\n";
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
            if (!DB::getSchemaBuilder()->hasTable($tabla)) {
                return "-- Tabla {$tabla} no existe en este esquema\n\n";
            }

            $filas = DB::table($tabla)->get();

            $out  = "-- ────────────────────────────────────────────────────────\n";
            $out .= "-- Tabla: {$tabla} ({$filas->count()} registros)\n";
            $out .= "-- ────────────────────────────────────────────────────────\n";

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

    public function importarSql(string $sqlContent): array
    {
        $ejecutadas = 0;
        $errores    = [];

        // Limpiar comentarios de bloque
        $sqlContent = preg_replace('!/\*.*?\*/!s', '', $sqlContent);

        // Dividir por líneas
        $lineas = explode("\n", $sqlContent);
        $buffer = '';

        DB::beginTransaction();

        try {
            foreach ($lineas as $linea) {
                $trimLinea = trim($linea);

                // Saltar líneas vacías o comentarios de una sola línea
                if ($trimLinea === '' || str_starts_with($trimLinea, '--') || str_starts_with($trimLinea, '#')) {
                    continue;
                }

                $buffer .= ' ' . $trimLinea;

                // Si termina en punto y coma, ejecutar la sentencia
                if (str_ends_with($trimLinea, ';')) {
                    $sentencia = trim($buffer);
                    $buffer = '';

                    if (!empty($sentencia)) {
                        try {
                            DB::unprepared($sentencia);
                            $ejecutadas++;
                        } catch (\Exception $ex) {
                            $errores[] = "Error en comando: " . substr($sentencia, 0, 100) . "... Detalle: " . $ex->getMessage();
                        }
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error crítico durante importación de BD: " . $e->getMessage());
            throw $e;
        }

        return [
            'ejecutadas' => $ejecutadas,
            'errores'    => $errores,
        ];
    }
}
