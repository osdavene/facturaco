<?php
namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Cotizacion;
use App\Models\Remision;
use App\Models\ReciboCaja;
use App\Models\OrdenCompra;
use App\Models\NotaCredito;
use Illuminate\Http\Request;

class BusquedaController extends Controller
{
    public function buscar(Request $request)
    {
        $q = trim($request->q ?? '');

        if (strlen($q) < 2) {
            return response()->json(['resultados' => [], 'query' => $q]);
        }

        $resultados = [];

        // ── Facturas ──────────────────────────────────
        Factura::where(function($query) use ($q) {
                $query->where('numero', 'like', "%$q%")
                      ->orWhere('cliente_nombre', 'like', "%$q%");
            })
            ->limit(4)->get()
            ->each(function($f) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Factura',
                    'icono'      => 'fa-file-invoice',
                    'color'      => 'amber',
                    'titulo'     => $f->numero,
                    'subtitulo'  => $f->cliente_nombre,
                    'detalle'    => '$'.number_format($f->total,0,',','.').' · '.ucfirst($f->estado),
                    'url'        => route('facturas.show', $f),
                ];
            });

        // ── Clientes ──────────────────────────────────
        Cliente::where('activo', true)
            ->where(function($query) use ($q) {
                $query->where('razon_social',      'like', "%$q%")
                      ->orWhere('nombres',          'like', "%$q%")
                      ->orWhere('apellidos',        'like', "%$q%")
                      ->orWhere('numero_documento', 'like', "%$q%");
            })
            ->limit(3)->get()
            ->each(function($c) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Cliente',
                    'icono'      => 'fa-user',
                    'color'      => 'blue',
                    'titulo'     => $c->nombre_completo,
                    'subtitulo'  => $c->tipo_documento.': '.$c->numero_documento,
                    'detalle'    => $c->municipio ?? '',
                    'url'        => route('clientes.show', $c),
                ];
            });

        // ── Productos ─────────────────────────────────
        Producto::where('activo', true)
            ->where(function($query) use ($q) {
                $query->where('nombre', 'like', "%$q%")
                      ->orWhere('codigo', 'like', "%$q%");
            })
            ->limit(3)->get()
            ->each(function($p) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Producto',
                    'icono'      => 'fa-box',
                    'color'      => 'emerald',
                    'titulo'     => $p->nombre,
                    'subtitulo'  => 'Código: '.$p->codigo,
                    'detalle'    => $p->es_servicio ? 'Servicio' : 'Stock: '.$p->stock_actual,
                    'url'        => route('inventario.show', $p),
                ];
            });

        // ── Proveedores ───────────────────────────────
        Proveedor::where('activo', true)
            ->where(function($query) use ($q) {
                $query->where('razon_social',      'like', "%$q%")
                      ->orWhere('numero_documento', 'like', "%$q%");
            })
            ->limit(2)->get()
            ->each(function($p) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Proveedor',
                    'icono'      => 'fa-truck',
                    'color'      => 'purple',
                    'titulo'     => $p->razon_social,
                    'subtitulo'  => $p->tipo_documento.': '.$p->numero_documento,
                    'detalle'    => $p->municipio ?? '',
                    'url'        => route('proveedores.show', $p),
                ];
            });

        // ── Cotizaciones ──────────────────────────────
        Cotizacion::where(function($query) use ($q) {
                $query->where('numero', 'like', "%$q%")
                      ->orWhere('cliente_nombre', 'like', "%$q%");
            })
            ->limit(2)->get()
            ->each(function($c) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Cotización',
                    'icono'      => 'fa-file-alt',
                    'color'      => 'cyan',
                    'titulo'     => $c->numero,
                    'subtitulo'  => $c->cliente_nombre,
                    'detalle'    => '$'.number_format($c->total,0,',','.').' · '.ucfirst($c->estado),
                    'url'        => route('cotizaciones.show', $c),
                ];
            });

        // ── Remisiones ────────────────────────────────
        Remision::where(function($query) use ($q) {
                $query->where('numero', 'like', "%$q%")
                      ->orWhere('cliente_nombre', 'like', "%$q%");
            })
            ->limit(2)->get()
            ->each(function($r) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Remisión',
                    'icono'      => 'fa-receipt',
                    'color'      => 'orange',
                    'titulo'     => $r->numero,
                    'subtitulo'  => $r->cliente_nombre,
                    'detalle'    => ucfirst($r->estado),
                    'url'        => route('remisiones.show', $r),
                ];
            });

        // ── Recibos de Caja ───────────────────────────
        ReciboCaja::where(function($query) use ($q) {
                $query->where('numero', 'like', "%$q%")
                      ->orWhere('cliente_nombre', 'like', "%$q%");
            })
            ->limit(2)->get()
            ->each(function($r) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Recibo',
                    'icono'      => 'fa-hand-holding-usd',
                    'color'      => 'green',
                    'titulo'     => $r->numero,
                    'subtitulo'  => $r->cliente_nombre,
                    'detalle'    => '$'.number_format($r->valor,0,',','.'),
                    'url'        => route('recibos.show', $r),
                ];
            });

        // ── Órdenes de Compra ─────────────────────────
        OrdenCompra::where(function($query) use ($q) {
                $query->where('numero', 'like', "%$q%")
                      ->orWhere('proveedor_nombre', 'like', "%$q%");
            })
            ->limit(2)->get()
            ->each(function($o) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Orden Compra',
                    'icono'      => 'fa-shopping-cart',
                    'color'      => 'slate',
                    'titulo'     => $o->numero,
                    'subtitulo'  => $o->proveedor_nombre,
                    'detalle'    => '$'.number_format($o->total,0,',','.').' · '.ucfirst($o->estado),
                    'url'        => route('ordenes.show', $o),
                ];
            });

        // ── Notas de Crédito ──────────────────────────
        NotaCredito::where(function($query) use ($q) {
                $query->where('numero', 'like', "%$q%")
                      ->orWhere('cliente_nombre', 'like', "%$q%")
                      ->orWhere('factura_numero', 'like', "%$q%");
            })
            ->limit(2)->get()
            ->each(function($n) use (&$resultados) {
                $resultados[] = [
                    'tipo'       => 'Nota Crédito',
                    'icono'      => 'fa-file-invoice',
                    'color'      => 'violet',
                    'titulo'     => $n->numero,
                    'subtitulo'  => $n->cliente_nombre,
                    'detalle'    => 'Factura: '.$n->factura_numero.' · $'.number_format($n->total,0,',','.'),
                    'url'        => route('notas_credito.show', $n),
                ];
            });

        return response()->json([
            'resultados' => $resultados,
            'query'      => $q,
            'total'      => count($resultados),
        ]);
    }
}