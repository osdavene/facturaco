<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;

class BackupController extends Controller
{
    // ── Tablas disponibles para backup de empresa ─────────────────────────
    private function tablas(): array
    {
        return [
            'clientes'               => 'Clientes',
            'proveedores'            => 'Proveedores',
            'productos'              => 'Productos',
            'categorias'             => 'Categorías',
            'unidades_medida'        => 'Unidades de Medida',
            'facturas'               => 'Facturas',
            'factura_items'          => 'Ítems de Facturas',
            'cotizaciones'           => 'Cotizaciones',
            'cotizacion_items'       => 'Ítems de Cotizaciones',
            'ordenes_compra'         => 'Órdenes de Compra',
            'orden_compra_items'     => 'Ítems de Órdenes',
            'recibos_caja'           => 'Recibos de Caja',
            'remisiones'             => 'Remisiones',
            'remision_items'         => 'Ítems de Remisiones',
            'movimientos_inventario' => 'Movimientos de Inventario',
        ];
    }

    // ── IDs de empresa activa + filiales (desde sesión) ───────────────────
    private function empresaIds(): array
    {
        $ids = session('empresa_grupo_ids', []);
        if (empty($ids)) {
            $id = session('empresa_activa_id');
            $ids = $id ? [(int) $id] : [];
        }
        return $ids;
    }

    // ── Aplica filtro de empresa a la query según la tabla ─────────────────
    private function filtrar(string $tabla, $query, array $ids): mixed
    {
        if (empty($ids)) {
            return $query->whereRaw('1=0');
        }

        return match ($tabla) {
            'factura_items'      => $query->whereIn('factura_id',
                                        DB::table('facturas')->whereIn('empresa_id', $ids)->pluck('id')),
            'cotizacion_items'   => $query->whereIn('cotizacion_id',
                                        DB::table('cotizaciones')->whereIn('empresa_id', $ids)->pluck('id')),
            'remision_items'     => $query->whereIn('remision_id',
                                        DB::table('remisiones')->whereIn('empresa_id', $ids)->pluck('id')),
            'orden_compra_items' => $query->whereIn('orden_compra_id',
                                        DB::table('ordenes_compra')->whereIn('empresa_id', $ids)->pluck('id')),
            default              => $query->whereIn('empresa_id', $ids),
        };
    }

    // ── Vista principal ───────────────────────────────────────────────────
    public function index()
    {
        $ids     = $this->empresaIds();
        $tablas  = $this->tablas();
        $empresa = \App\Models\Empresa::find(session('empresa_activa_id'));

        $conteos = [];
        foreach (array_keys($tablas) as $tabla) {
            try {
                $q = DB::table($tabla);
                $conteos[$tabla] = $this->filtrar($tabla, $q, $ids)->count();
            } catch (\Exception) {
                $conteos[$tabla] = 0;
            }
        }

        return view('backup.index', compact('tablas', 'conteos', 'empresa'));
    }

    // ── Backup JSON filtrado por empresa ──────────────────────────────────
    public function descargarJson()
    {
        $ids     = $this->empresaIds();
        $empresa = \App\Models\Empresa::find(session('empresa_activa_id'));
        $datos   = [];

        foreach (array_keys($this->tablas()) as $tabla) {
            try {
                $q = DB::table($tabla);
                $datos[$tabla] = $this->filtrar($tabla, $q, $ids)->get()->toArray();
            } catch (\Exception) {
                $datos[$tabla] = [];
            }
        }

        $payload = json_encode([
            'sistema'      => 'FacturaCO',
            'empresa'      => $empresa->razon_social ?? 'N/A',
            'fecha'        => now()->format('Y-m-d H:i:s'),
            'generado_por' => auth()->user()->name,
            'version'      => '1.0',
            'datos'        => $datos,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $slug   = \Illuminate\Support\Str::slug($empresa->razon_social ?? 'empresa');
        $nombre = 'backup_' . $slug . '_' . now()->format('Y-m-d_His') . '.json';

        return response($payload, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $nombre . '"',
        ]);
    }

    // ── Backup CSV/ZIP selectivo filtrado por empresa ─────────────────────
    public function descargarCsv(Request $request)
    {
        $request->validate([
            'tablas'      => 'required|array|min:1',
            'tablas.*'    => 'string',
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date',
        ]);

        $ids                 = $this->empresaIds();
        $tablasDisponibles   = array_keys($this->tablas());
        $tablasSeleccionadas = array_intersect($request->tablas, $tablasDisponibles);

        if (empty($tablasSeleccionadas)) {
            return back()->with('error', 'Selecciona al menos una tabla.');
        }

        $empresa    = \App\Models\Empresa::find(session('empresa_activa_id'));
        $tmpDir     = sys_get_temp_dir() . '/backup_' . time();
        mkdir($tmpDir);

        $tablasFecha = ['facturas', 'cotizaciones', 'ordenes_compra', 'recibos_caja',
                        'remisiones', 'movimientos_inventario'];

        foreach ($tablasSeleccionadas as $tabla) {
            $query = DB::table($tabla);
            $query = $this->filtrar($tabla, $query, $ids);

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

            $csv  = '';
            $cols = array_keys((array) $filas->first());
            $csv .= implode(',', array_map(fn($c) => '"' . $c . '"', $cols)) . "\n";

            foreach ($filas as $fila) {
                $valores = array_map(function ($v) {
                    if (is_null($v))  return '';
                    if (is_bool($v))  return $v ? '1' : '0';
                    $v = str_replace('"', '""', (string) $v);
                    return '"' . $v . '"';
                }, (array) $fila);
                $csv .= implode(',', $valores) . "\n";
            }

            file_put_contents($tmpDir . '/' . $tabla . '.csv', $csv);
        }

        $zipPath = sys_get_temp_dir() . '/backup_' . now()->format('Y-m-d_His') . '.zip';
        $zip     = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach (glob($tmpDir . '/*.csv') as $archivo) {
            $zip->addFile($archivo, basename($archivo));
        }

        $readme  = "BACKUP FACTURACO\n";
        $readme .= "Empresa: " . ($empresa->razon_social ?? 'N/A') . "\n";
        $readme .= "Fecha: "   . now()->format('d/m/Y H:i:s') . "\n";
        $readme .= "Generado por: " . auth()->user()->name . "\n";
        $readme .= "Módulos: "  . implode(', ', $tablasSeleccionadas) . "\n";
        if ($request->filled('fecha_desde') || $request->filled('fecha_hasta')) {
            $readme .= "Rango: " . ($request->fecha_desde ?? '—') . ' al ' . ($request->fecha_hasta ?? '—') . "\n";
        }
        $zip->addFromString('LEEME.txt', $readme);
        $zip->close();

        array_map('unlink', glob($tmpDir . '/*.csv'));
        rmdir($tmpDir);

        $slug   = \Illuminate\Support\Str::slug($empresa->razon_social ?? 'empresa');
        $nombre = 'backup_' . $slug . '_' . now()->format('Y-m-d_His') . '.zip';

        return response()->download($zipPath, $nombre)->deleteFileAfterSend(true);
    }
}
