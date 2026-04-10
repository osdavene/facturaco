<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuditoriaController extends Controller
{
    public function index(Request $request)
    {
        $usuarios = User::orderBy('name')->get(['id', 'name', 'email']);

        // Construimos una consulta unificada con UNION de todas las tablas
        $registros = collect();

        // ── Clientes ──────────────────────────────────────────
        $clientes = DB::table('clientes')
            ->join('users as cu', 'clientes.created_by', '=', 'cu.id')
            ->leftJoin('users as uu', 'clientes.updated_by', '=', 'uu.id')
            ->select(
                DB::raw("'Cliente' as modulo"),
                DB::raw("COALESCE(clientes.razon_social, CONCAT(clientes.nombres, ' ', clientes.apellidos)) as descripcion"),
                'cu.name as creado_por',
                'uu.name as actualizado_por',
                'clientes.created_at',
                'clientes.updated_at',
                DB::raw("'clientes' as tabla"),
                'clientes.id'
            )
            ->whereNotNull('clientes.created_by');

        // ── Proveedores ───────────────────────────────────────
        $proveedores = DB::table('proveedores')
            ->join('users as cu', 'proveedores.created_by', '=', 'cu.id')
            ->leftJoin('users as uu', 'proveedores.updated_by', '=', 'uu.id')
            ->select(
                DB::raw("'Proveedor' as modulo"),
                DB::raw("proveedores.razon_social as descripcion"),
                'cu.name as creado_por',
                'uu.name as actualizado_por',
                'proveedores.created_at',
                'proveedores.updated_at',
                DB::raw("'proveedores' as tabla"),
                'proveedores.id'
            )
            ->whereNotNull('proveedores.created_by');

        // ── Productos ─────────────────────────────────────────
        $productos = DB::table('productos')
            ->join('users as cu', 'productos.created_by', '=', 'cu.id')
            ->leftJoin('users as uu', 'productos.updated_by', '=', 'uu.id')
            ->select(
                DB::raw("'Producto' as modulo"),
                DB::raw("productos.nombre as descripcion"),
                'cu.name as creado_por',
                'uu.name as actualizado_por',
                'productos.created_at',
                'productos.updated_at',
                DB::raw("'productos' as tabla"),
                'productos.id'
            )
            ->whereNotNull('productos.created_by');

        // ── Categorías ────────────────────────────────────────
        $categorias = DB::table('categorias')
            ->join('users as cu', 'categorias.created_by', '=', 'cu.id')
            ->leftJoin('users as uu', 'categorias.updated_by', '=', 'uu.id')
            ->select(
                DB::raw("'Categoría' as modulo"),
                DB::raw("categorias.nombre as descripcion"),
                'cu.name as creado_por',
                'uu.name as actualizado_por',
                'categorias.created_at',
                'categorias.updated_at',
                DB::raw("'categorias' as tabla"),
                'categorias.id'
            )
            ->whereNotNull('categorias.created_by');

        // ── Facturas ──────────────────────────────────────────
        $facturas = DB::table('facturas')
            ->join('users as cu', 'facturas.user_id', '=', 'cu.id')
            ->select(
                DB::raw("'Factura' as modulo"),
                DB::raw("CONCAT('Factura #', facturas.numero, ' — ', facturas.cliente_nombre) as descripcion"),
                'cu.name as creado_por',
                DB::raw("NULL as actualizado_por"),
                'facturas.created_at',
                'facturas.updated_at',
                DB::raw("'facturas' as tabla"),
                'facturas.id'
            );

        // ── Cotizaciones ──────────────────────────────────────
        $cotizaciones = DB::table('cotizaciones')
            ->join('users as cu', 'cotizaciones.user_id', '=', 'cu.id')
            ->select(
                DB::raw("'Cotización' as modulo"),
                DB::raw("CONCAT('Cotización #', cotizaciones.numero, ' — ', cotizaciones.cliente_nombre) as descripcion"),
                'cu.name as creado_por',
                DB::raw("NULL as actualizado_por"),
                'cotizaciones.created_at',
                'cotizaciones.updated_at',
                DB::raw("'cotizaciones' as tabla"),
                'cotizaciones.id'
            );

        // ── Órdenes de Compra ─────────────────────────────────
        $ordenes = DB::table('ordenes_compra')
            ->join('users as cu', 'ordenes_compra.user_id', '=', 'cu.id')
            ->select(
                DB::raw("'Orden de Compra' as modulo"),
                DB::raw("CONCAT('Orden #', ordenes_compra.numero) as descripcion"),
                'cu.name as creado_por',
                DB::raw("NULL as actualizado_por"),
                'ordenes_compra.created_at',
                'ordenes_compra.updated_at',
                DB::raw("'ordenes_compra' as tabla"),
                'ordenes_compra.id'
            );

        // ── Recibos de Caja ───────────────────────────────────
        $recibos = DB::table('recibos_caja')
            ->join('users as cu', 'recibos_caja.user_id', '=', 'cu.id')
            ->select(
                DB::raw("'Recibo de Caja' as modulo"),
                DB::raw("CONCAT('Recibo #', recibos_caja.numero) as descripcion"),
                'cu.name as creado_por',
                DB::raw("NULL as actualizado_por"),
                'recibos_caja.created_at',
                'recibos_caja.updated_at',
                DB::raw("'recibos_caja' as tabla"),
                'recibos_caja.id'
            );

        // ── Remisiones ────────────────────────────────────────
        $remisiones = DB::table('remisiones')
            ->join('users as cu', 'remisiones.user_id', '=', 'cu.id')
            ->select(
                DB::raw("'Remisión' as modulo"),
                DB::raw("CONCAT('Remisión #', remisiones.numero) as descripcion"),
                'cu.name as creado_por',
                DB::raw("NULL as actualizado_por"),
                'remisiones.created_at',
                'remisiones.updated_at',
                DB::raw("'remisiones' as tabla"),
                'remisiones.id'
            );

        // ── Unir todo ─────────────────────────────────────────
        $query = $clientes
            ->union($proveedores)
            ->union($productos)
            ->union($categorias)
            ->union($facturas)
            ->union($cotizaciones)
            ->union($ordenes)
            ->union($recibos)
            ->union($remisiones);

        // Envolver en subquery para poder filtrar y paginar
        $subquery = DB::table(DB::raw("({$query->toSql()}) as auditoria"))
            ->mergeBindings($query);

        // ── Filtros ───────────────────────────────────────────
        if ($request->filled('modulo')) {
            $subquery->where('modulo', $request->modulo);
        }

        if ($request->filled('usuario')) {
            $subquery->where('creado_por', 'like', '%' . $request->usuario . '%');
        }

        if ($request->filled('fecha_desde')) {
            $subquery->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $subquery->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $registros = $subquery
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        $modulos = [
            'Cliente', 'Proveedor', 'Producto', 'Categoría',
            'Factura', 'Cotización', 'Orden de Compra',
            'Recibo de Caja', 'Remisión',
        ];

        return view('auditoria.index', compact('registros', 'usuarios', 'modulos'));
    }
}