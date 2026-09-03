<?php

namespace App\Actions;

use App\Models\Empresa;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class GenerarBackupAction
{
    public static function tablas(): array
    {
        return [
            'clientes'                => 'Clientes',
            'proveedores'             => 'Proveedores',
            'productos'               => 'Productos y Servicios',
            'categorias'              => 'Categorías de Productos',
            'unidades_medida'         => 'Unidades de Medida',
            'facturas'                => 'Facturas Electrónicas',
            'factura_items'           => 'Ítems de Facturas',
            'notas_credito'           => 'Notas Crédito',
            'nota_credito_items'      => 'Ítems de Notas Crédito',
            'cotizaciones'            => 'Cotizaciones',
            'cotizacion_items'        => 'Ítems de Cotizaciones',
            'ordenes_compra'          => 'Órdenes de Compra',
            'orden_compra_items'      => 'Ítems de Órdenes',
            'recibos_caja'            => 'Recibos de Caja (Cobros)',
            'remisiones'              => 'Remisiones de Entrega',
            'remision_items'          => 'Ítems de Remisiones',
            'movimientos_inventario'  => 'Movimientos de Inventario (Kárdex)',
            'asientos_contables'      => 'Asientos Contables (Libro Diario)',
            'asiento_contable_lineas' => 'Líneas de Asientos Contables',
            'puc_cuentas'             => 'Plan Único de Cuentas (PUC)',
            'empleados'               => 'Empleados y Talento Humano',
            'nominas'                 => 'Períodos de Nómina',
            'nomina_empleado'         => 'Liquidaciones de Nómina',
            'resoluciones_dian'       => 'Resoluciones de Facturación DIAN',
        ];
    }

    public static function empresaIds(): array
    {
        $ids = session('empresa_grupo_ids', []);
        if (empty($ids)) {
            $id = session('empresa_activa_id');
            $ids = $id ? [(int) $id] : [];
        }
        return $ids;
    }

    public static function filtrar(string $tabla, $query, array $ids)
    {
        if (empty($ids)) {
            return $query->whereRaw('1=0');
        }

        return match ($tabla) {
            'factura_items'           => $query->whereIn('factura_id', DB::table('facturas')->whereIn('empresa_id', $ids)->pluck('id')),
            'nota_credito_items'      => $query->whereIn('nota_credito_id', DB::table('notas_credito')->whereIn('empresa_id', $ids)->pluck('id')),
            'cotizacion_items'        => $query->whereIn('cotizacion_id', DB::table('cotizaciones')->whereIn('empresa_id', $ids)->pluck('id')),
            'remision_items'          => $query->whereIn('remision_id', DB::table('remisiones')->whereIn('empresa_id', $ids)->pluck('id')),
            'orden_compra_items'      => $query->whereIn('orden_compra_id', DB::table('ordenes_compra')->whereIn('empresa_id', $ids)->pluck('id')),
            'asiento_contable_lineas' => $query->whereIn('asiento_contable_id', DB::table('asientos_contables')->whereIn('empresa_id', $ids)->pluck('id')),
            'nomina_empleado'         => $query->whereIn('nomina_id', DB::table('nominas')->whereIn('empresa_id', $ids)->pluck('id')),
            'puc_cuentas'             => $query->where(function ($q) use ($ids) {
                                            $q->whereIn('empresa_id', $ids)->orWhereNull('empresa_id');
                                         }),
            default                   => $query->whereIn('empresa_id', $ids),
        };
    }

    public static function indexData(): array
    {
        $ids = self::empresaIds();
        $tablas = self::tablas();
        $empresa = Empresa::find(session('empresa_activa_id'));

        $conteos = [];
        foreach (array_keys($tablas) as $tabla) {
            try {
                $q = DB::table($tabla);
                $conteos[$tabla] = self::filtrar($tabla, $q, $ids)->count();
            } catch (\Exception) {
                $conteos[$tabla] = 0;
            }
        }

        return compact('tablas', 'conteos', 'empresa');
    }

    public static function descargarJson()
    {
        $ids = self::empresaIds();
        $empresa = Empresa::find(session('empresa_activa_id'));
        $datos = [];

        foreach (array_keys(self::tablas()) as $tabla) {
            try {
                $q = DB::table($tabla);
                $datos[$tabla] = self::filtrar($tabla, $q, $ids)->get()->toArray();
            } catch (\Exception) {
                $datos[$tabla] = [];
            }
        }

        $payload = json_encode([
            'sistema'      => 'FacCol',
            'empresa'      => $empresa->razon_social ?? 'N/A',
            'nit'          => ($empresa->nit ?? '') . '-' . ($empresa->digito_verificacion ?? ''),
            'fecha'        => now()->format('Y-m-d H:i:s'),
            'generado_por' => auth()->user()->name ?? 'Administrador',
            'version'      => '2.0',
            'datos'        => $datos,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $slug = \Illuminate\Support\Str::slug($empresa->razon_social ?? 'empresa');
        $nombre = 'backup_' . $slug . '_' . now()->format('Y-m-d_His') . '.json';

        return response($payload, 200, [
            'Content-Type'        => 'application/json; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }

    public static function descargarSql()
    {
        $ids     = self::empresaIds();
        $empresa = Empresa::find(session('empresa_activa_id'));
        $tablas  = array_keys(self::tablas());

        $sql  = "-- ========================================================\n";
        $sql .= "-- RESPALDO SQL EXCLUSIVO DE EMPRESA — FacCol\n";
        $sql .= "-- Empresa:      " . ($empresa->razon_social ?? 'N/A') . " (NIT: " . ($empresa->nit ?? '') . ")\n";
        $sql .= "-- Fecha:        " . now()->format('d/m/Y H:i:s') . "\n";
        $sql .= "-- Generado por: " . (auth()->user()->name ?? 'Administrador') . "\n";
        $sql .= "-- ========================================================\n\n";
        $sql .= "SET client_encoding = 'UTF8';\n";
        $sql .= "SET standard_conforming_strings = on;\n\n";

        foreach ($tablas as $tabla) {
            try {
                $q = DB::table($tabla);
                $filas = self::filtrar($tabla, $q, $ids)->get();

                $sql .= "-- ────────────────────────────────────────────────────────\n";
                $sql .= "-- Tabla: {$tabla} (" . $filas->count() . " registros)\n";
                $sql .= "-- ────────────────────────────────────────────────────────\n";

                if ($filas->isEmpty()) {
                    $sql .= "-- (sin registros para esta empresa)\n\n";
                    continue;
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

                    $sql .= "INSERT INTO \"{$tabla}\" ({$colsSql}) VALUES (" . implode(', ', $vals) . ");\n";
                }
                $sql .= "\n";
            } catch (\Exception $e) {
                $sql .= "-- ERROR en {$tabla}: " . $e->getMessage() . "\n\n";
            }
        }

        $slug   = \Illuminate\Support\Str::slug($empresa->razon_social ?? 'empresa');
        $nombre = 'backup_' . $slug . '_' . now()->format('Y-m-d_His') . '.sql';

        return response($sql, 200, [
            'Content-Type'        => 'application/sql; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }

    public static function descargarCsv(Request $request)
    {
        $ids = self::empresaIds();
        $tablasDisponibles = array_keys(self::tablas());
        $tablasSeleccionadas = array_intersect($request->input('tablas', []), $tablasDisponibles);

        if (empty($tablasSeleccionadas)) {
            throw new \Exception('Selecciona al menos una tabla para exportar.');
        }

        $empresa = Empresa::find(session('empresa_activa_id'));
        $tmpDir = sys_get_temp_dir() . '/backup_' . uniqid();
        mkdir($tmpDir, 0755, true);

        $tablasFecha = ['facturas', 'notas_credito', 'cotizaciones', 'ordenes_compra', 'recibos_caja', 'remisiones', 'movimientos_inventario', 'asientos_contables'];

        foreach ($tablasSeleccionadas as $tabla) {
            $query = DB::table($tabla);
            $query = self::filtrar($tabla, $query, $ids);

            if (in_array($tabla, $tablasFecha)) {
                if ($request->filled('fecha_desde')) {
                    $query->whereDate('created_at', '>=', $request->fecha_desde);
                }
                if ($request->filled('fecha_hasta')) {
                    $query->whereDate('created_at', '<=', $request->fecha_hasta);
                }
            }

            $filas = $query->get();
            if ($filas->isEmpty()) continue;

            $csv = "\xEF\xBB\xBF"; // UTF-8 BOM
            $cols = array_keys((array) $filas->first());
            $csv .= implode(';', array_map(fn($c) => '"' . $c . '"', $cols)) . "\n";

            foreach ($filas as $fila) {
                $valores = array_map(function ($v) {
                    if (is_null($v)) return '';
                    if (is_bool($v)) return $v ? '1' : '0';
                    $v = str_replace('"', '""', (string) $v);
                    return '"' . $v . '"';
                }, (array) $fila);
                $csv .= implode(';', $valores) . "\n";
            }

            file_put_contents($tmpDir . '/' . $tabla . '.csv', $csv);
        }

        $slug    = \Illuminate\Support\Str::slug($empresa->razon_social ?? 'empresa');
        $zipPath = sys_get_temp_dir() . '/backup_' . $slug . '_' . now()->format('Y-m-d_His') . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach (glob($tmpDir . '/*.csv') as $archivo) {
            $zip->addFile($archivo, basename($archivo));
        }

        $readme  = "COPIA DE SEGURIDAD FACCOL\n";
        $readme .= "Empresa: " . ($empresa->razon_social ?? 'N/A') . " (NIT: " . ($empresa->nit ?? '') . ")\n";
        $readme .= "Fecha: " . now()->format('d/m/Y H:i:s') . "\n";
        $readme .= "Generado por: " . (auth()->user()->name ?? 'Administrador') . "\n";
        $readme .= "Módulos exportados: " . implode(', ', $tablasSeleccionadas) . "\n";
        if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
            $readme .= "Rango de fechas: " . ($request->fecha_desde ?? 'Inicio') . ' al ' . ($request->fecha_hasta ?? 'Hoy') . "\n";
        }
        $zip->addFromString('LEEME.txt', $readme);
        $zip->close();

        array_map('unlink', glob($tmpDir . '/*.csv'));
        rmdir($tmpDir);

        $nombre = 'backup_' . $slug . '_' . now()->format('Y-m-d_His') . '.zip';

        return response()->download($zipPath, $nombre)->deleteFileAfterSend(true);
    }
}
