<?php
namespace App\Http\Controllers;

use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdenCompraController extends Controller
{
    public function index(Request $request)
    {
        $ordenes = OrdenCompra::with('proveedor')
            ->when($request->buscar, function($q) use ($request) {
                $q->where('numero', 'like', '%'.$request->buscar.'%')
                  ->orWhere('proveedor_nombre', 'like', '%'.$request->buscar.'%');
            })
            ->when($request->estado, fn($q) => $q->where('estado', $request->estado))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $totales = [
            'total'    => OrdenCompra::count(),
            'borrador' => OrdenCompra::where('estado', 'borrador')->count(),
            'aprobada' => OrdenCompra::where('estado', 'aprobada')->count(),
            'recibida' => OrdenCompra::where('estado', 'recibida')->count(),
        ];

        return view('ordenes.index', compact('ordenes', 'totales'));
    }

    public function create()
    {
        $consecutivo = OrdenCompra::siguienteConsecutivo();
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        return view('ordenes.create', compact('consecutivo', 'proveedores'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proveedor_id'    => 'required|exists:proveedores,id',
            'fecha_emision'   => 'required|date',
            'fecha_esperada'  => 'nullable|date|after_or_equal:fecha_emision',
            'items'           => 'required|array|min:1',
            'items.*.descripcion'    => 'required|string',
            'items.*.cantidad'       => 'required|numeric|min:0.001',
            'items.*.precio_unitario'=> 'required|numeric|min:0',
        ]);

        $userId = auth()->id();

        DB::transaction(function() use ($request, $userId) {
            $proveedor   = Proveedor::findOrFail($request->proveedor_id);
            $consecutivo = OrdenCompra::siguienteConsecutivo();

            $subtotal = 0; $totalIva = 0; $totalDesc = 0;

            foreach ($request->items as $item) {
                $cant    = floatval($item['cantidad']);
                $precio  = floatval($item['precio_unitario']);
                $descPct = floatval($item['descuento_pct'] ?? 0);
                $ivaPct  = floatval($item['iva_pct'] ?? 19);

                $sub     = $cant * $precio;
                $desc    = $sub * ($descPct / 100);
                $base    = $sub - $desc;
                $iva     = $base * ($ivaPct / 100);

                $subtotal   += $base;
                $totalIva   += $iva;
                $totalDesc  += $desc;
            }

            $orden = OrdenCompra::create([
                'numero'              => $consecutivo['numero'],
                'consecutivo'         => $consecutivo['consecutivo'],
                'proveedor_id'        => $proveedor->id,
                'proveedor_nombre'    => $proveedor->razon_social,
                'proveedor_documento' => $proveedor->tipo_documento.': '.$proveedor->documento_formateado,
                'fecha_emision'       => $request->fecha_emision,
                'fecha_esperada'      => $request->fecha_esperada,
                'subtotal'            => $subtotal,
                'iva'                 => $totalIva,
                'descuento'           => $totalDesc,
                'total'               => $subtotal + $totalIva,
                'estado'              => $request->estado ?? 'borrador',
                'forma_pago'          => $request->forma_pago ?? 'credito',
                'plazo_pago'          => $request->plazo_pago ?? 30,
                'observaciones'       => $request->observaciones,
                'user_id'             => $userId,
            ]);

            foreach ($request->items as $i => $item) {
                $cant    = floatval($item['cantidad']);
                $precio  = floatval($item['precio_unitario']);
                $descPct = floatval($item['descuento_pct'] ?? 0);
                $ivaPct  = floatval($item['iva_pct'] ?? 19);

                $sub  = $cant * $precio;
                $desc = $sub * ($descPct / 100);
                $base = $sub - $desc;
                $iva  = $base * ($ivaPct / 100);

                OrdenCompraItem::create([
                    'orden_compra_id' => $orden->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo'] ?? 'SIN-COD',
                    'descripcion'     => $item['descripcion'],
                    'unidad'          => $item['unidad'] ?? 'UN',
                    'cantidad'        => $cant,
                    'precio_unitario' => $precio,
                    'descuento_pct'   => $descPct,
                    'descuento'       => $desc,
                    'subtotal'        => $base,
                    'iva_pct'         => $ivaPct,
                    'iva'             => $iva,
                    'total'           => $base + $iva,
                    'orden'           => $i,
                ]);
            }
        });

        return redirect()->route('ordenes.index')
            ->with('success', 'Orden de compra creada correctamente.');
    }

    public function show(OrdenCompra $orden)
    {
        $orden->load(['items.producto', 'proveedor', 'usuario']);
        return view('ordenes.show', compact('orden'));
    }

    public function edit(OrdenCompra $orden)
    {
        if (!in_array($orden->estado, ['borrador'])) {
            return redirect()->route('ordenes.show', $orden)
                ->with('error', 'Solo puedes editar órdenes en borrador.');
        }
        $orden->load('items');
        $proveedores = Proveedor::where('activo', true)->orderBy('razon_social')->get();
        return view('ordenes.create', compact('orden', 'proveedores'));
    }

    public function update(Request $request, OrdenCompra $orden)
    {
        return redirect()->route('ordenes.show', $orden)
            ->with('success', 'Orden actualizada.');
    }

    public function destroy(OrdenCompra $orden)
    {
        $orden->update(['estado' => 'anulada']);
        return redirect()->route('ordenes.index')
            ->with('success', 'Orden anulada correctamente.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionó ningún elemento.');
        }
        $count = OrdenCompra::whereIn('id', $ids)->count();
        OrdenCompra::whereIn('id', $ids)->delete();
        return redirect()->route('ordenes.index')
            ->with('success', "{$count} orden(es) eliminada(s) correctamente.");
    }

    public function cambiarEstado(Request $request, OrdenCompra $orden)
    {
        $request->validate([
            'estado' => 'required|in:borrador,enviada,aprobada,recibida,anulada'
        ]);
        $orden->update(['estado' => $request->estado]);
        return back()->with('success', 'Estado actualizado.');
    }

    // Recibir mercancía y actualizar inventario
    public function recibir(Request $request, OrdenCompra $orden)
    {
        if ($orden->estado !== 'aprobada') {
            return back()->with('error', 'Solo puedes recibir órdenes aprobadas.');
        }

        $userId = auth()->id();

        DB::transaction(function() use ($request, $orden, $userId) {
            foreach ($orden->items as $item) {
                $cantRecibida = floatval($request->input('cantidad_'.$item->id, 0));
                if ($cantRecibida <= 0) continue;

                $item->update(['cantidad_recibida' => $cantRecibida]);

                // Actualizar stock si tiene producto
                if ($item->producto_id) {
                    $producto = Producto::find($item->producto_id);
                    if ($producto && !$producto->es_servicio) {
                        $stockAnterior = $producto->stock_actual;
                        $stockNuevo    = $stockAnterior + $cantRecibida;

                        $producto->update([
                            'stock_actual'  => $stockNuevo,
                            'precio_compra' => $item->precio_unitario,
                        ]);

                        MovimientoInventario::create([
                            'producto_id'    => $producto->id,
                            'tipo'           => 'entrada',
                            'cantidad'       => $cantRecibida,
                            'stock_anterior' => $stockAnterior,
                            'stock_nuevo'    => $stockNuevo,
                            'costo_unitario' => $item->precio_unitario,
                            'motivo'         => 'Recepción OC',
                            'referencia'     => $orden->numero,
                            'user_id'        => $userId,
                        ]);
                    }
                }
            }

            $orden->update([
                'estado'          => 'recibida',
                'fecha_recepcion' => now(),
                'notas_recepcion' => $request->notas_recepcion,
            ]);
        });

        return redirect()->route('ordenes.show', $orden)
            ->with('success', '¡Mercancía recibida! El inventario ha sido actualizado.');
    }

    public function pdf(OrdenCompra $orden)
    {
        $orden->load(['items', 'proveedor']);
        $empresa = Empresa::obtener();
        $pdf     = Pdf::loadView('ordenes.pdf', compact('orden', 'empresa'))
                      ->setPaper('a4', 'portrait');
        return $pdf->stream('orden-'.$orden->numero.'.pdf');
    }
}