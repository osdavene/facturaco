<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupController extends Controller
{
    // ── Configuración de tablas disponibles ───────────────────
    private function tablas(): array
    {
        return [
            'clientes'              => 'Clientes',
            'proveedores'           => 'Proveedores',
            'productos'             => 'Productos',
            'categorias'            => 'Categorías',
            'unidades_medida'       => 'Unidades de Medida',
            'facturas'              => 'Facturas',
            'factura_items'         => 'Ítems de Facturas',
            'cotizaciones'          => 'Cotizaciones',
            'cotizacion_items'      => 'Ítems de Cotizaciones',
            'ordenes_compra'        => 'Órdenes de Compra',
            'orden_compra_items'    => 'Ítems de Órdenes',
            'recibos_caja'          => 'Recibos de Caja',
            'remisiones'            => 'Remisiones',
            'remision_items'        => 'Ítems de Remisiones',
            'movimientos_inventario'=> 'Movimientos de Inventario',
            'users'                 => 'Usuarios',
            'empresa'               => 'Empresa',
            'login_logs'            => 'Historial de Accesos',
        ];
    }

    // ── Vista principal ───────────────────────────────────────
    public function index()
    {
        $tablas  = $this->tablas();
        $empresa = \App\Models\Empresa::first();

        // Conteos para mostrar en la UI
        $conteos = [];
        foreach (array_keys($tablas) as $tabla) {
            try {
                $conteos[$tabla] = DB::table($tabla)->count();
            } catch (\Exception $e) {
                $conteos[$tabla] = 0;
            }
        }

        return view('backup.index', compact('tablas', 'conteos', 'empresa'));
    }

    // ── OPCIÓN A: Backup completo JSON ────────────────────────
    public function descargarJson()
    {
        $datos   = [];
        $empresa = DB::table('empresa')->first();

        foreach (array_keys($this->tablas()) as $tabla) {
            try {
                $datos[$tabla] = DB::table($tabla)->get()->toArray();
            } catch (\Exception $e) {
                $datos[$tabla] = [];
            }
        }

        $payload = json_encode([
            'sistema'    => 'FacturaCO',
            'empresa'    => $empresa->razon_social ?? 'N/A',
            'fecha'      => now()->format('Y-m-d H:i:s'),
            'generado_por' => auth()->user()->name,
            'version'    => '1.0',
            'datos'      => $datos,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $nombre = 'backup_completo_' . now()->format('Y-m-d_His') . '.json';

        return response($payload, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }

    // ── OPCIÓN B: Backup selectivo CSV en ZIP ─────────────────
    public function descargarCsv(Request $request)
    {
        $request->validate([
            'tablas'      => 'required|array|min:1',
            'tablas.*'    => 'string',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
        ]);

        $tablasDisponibles = array_keys($this->tablas());
        $tablasSeleccionadas = array_intersect($request->tablas, $tablasDisponibles);

        if (empty($tablasSeleccionadas)) {
            return back()->with('error', 'Selecciona al menos una tabla.');
        }

        $tmpDir  = sys_get_temp_dir() . '/backup_' . time();
        mkdir($tmpDir);

        $tablasFecha = ['facturas','cotizaciones','ordenes_compra','recibos_caja','remisiones','movimientos_inventario','login_logs'];

        foreach ($tablasSeleccionadas as $tabla) {
            $query = DB::table($tabla);

            // Aplicar filtro de fechas solo a tablas transaccionales
            if (in_array($tabla, $tablasFecha)) {
                if ($request->filled('fecha_desde')) {
                    $query->whereDate('created_at', '>=', $request->fecha_desde);
                }
                if ($request->filled('fecha_hasta')) {
                    $query->whereDate('created_at', '<=', $request->fecha_hasta);
                }
            }

            $filas = $query->get();

            if ($filas->isEmpty()) {
                continue;
            }

            $csv  = '';
            $cols = array_keys((array) $filas->first());
            $csv .= implode(',', array_map(fn($c) => '"' . $c . '"', $cols)) . "\n";

            foreach ($filas as $fila) {
                $valores = array_map(function ($v) {
                    if (is_null($v))   return '';
                    if (is_bool($v))   return $v ? '1' : '0';
                    $v = str_replace('"', '""', (string) $v);
                    return '"' . $v . '"';
                }, (array) $fila);
                $csv .= implode(',', $valores) . "\n";
            }

            file_put_contents($tmpDir . '/' . $tabla . '.csv', $csv);
        }

        // Crear ZIP
        $zipPath = sys_get_temp_dir() . '/backup_csv_' . now()->format('Y-m-d_His') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach (glob($tmpDir . '/*.csv') as $archivo) {
            $zip->addFile($archivo, basename($archivo));
        }

        // Agregar un README
        $readme  = "BACKUP FACTURACO\n";
        $readme .= "Fecha: " . now()->format('d/m/Y H:i:s') . "\n";
        $readme .= "Generado por: " . auth()->user()->name . "\n";
        $readme .= "Módulos incluidos: " . implode(', ', $tablasSeleccionadas) . "\n";
        if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
            $readme .= "Rango de fechas: " . ($request->fecha_desde ?? '—') . " al " . ($request->fecha_hasta ?? '—') . "\n";
        }
        $zip->addFromString('LEEME.txt', $readme);
        $zip->close();

        // Limpiar temporales
        array_map('unlink', glob($tmpDir . '/*.csv'));
        rmdir($tmpDir);

        $nombre = 'backup_csv_' . now()->format('Y-m-d_His') . '.zip';

        return response()->download($zipPath, $nombre)->deleteFileAfterSend(true);
    }

    // ── OPCIÓN C: Backup SQL completo ─────────────────────────
    public function descargarSql()
    {
        $sql  = "-- ================================================\n";
        $sql .= "-- BACKUP SQL - FacturaCO\n";
        $sql .= "-- Fecha: " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "-- Generado por: " . auth()->user()->name . "\n";
        $sql .= "-- Base de datos: PostgreSQL\n";
        $sql .= "-- ================================================\n\n";
        $sql .= "SET client_encoding = 'UTF8';\n";
        $sql .= "SET standard_conforming_strings = on;\n\n";

        foreach (array_keys($this->tablas()) as $tabla) {
            try {
                $filas = DB::table($tabla)->get();

                $sql .= "-- ────────────────────────────────────────\n";
                $sql .= "-- Tabla: {$tabla}\n";
                $sql .= "-- ────────────────────────────────────────\n";

                if ($filas->isEmpty()) {
                    $sql .= "-- (sin datos)\n\n";
                    continue;
                }

                $sql .= "DELETE FROM \"{$tabla}\";\n";

                foreach ($filas as $fila) {
                    $cols    = array_keys((array) $fila);
                    $colsSql = implode(', ', array_map(fn($c) => '"' . $c . '"', $cols));

                    $vals = array_map(function ($v) {
                        if (is_null($v))            return 'NULL';
                        if (is_bool($v))            return $v ? 'TRUE' : 'FALSE';
                        if (is_int($v) || is_float($v)) return $v;
                        $v = str_replace("'", "''", (string) $v);
                        return "'" . $v . "'";
                    }, (array) $fila);

                    $valsSql = implode(', ', $vals);
                    $sql .= "INSERT INTO \"{$tabla}\" ({$colsSql}) VALUES ({$valsSql});\n";
                }

                $sql .= "\n";

            } catch (\Exception $e) {
                $sql .= "-- ERROR al exportar {$tabla}: " . $e->getMessage() . "\n\n";
            }
        }

        $nombre = 'backup_sql_' . now()->format('Y-m-d_His') . '.sql';

        return response($sql, 200, [
            'Content-Type'        => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }
}