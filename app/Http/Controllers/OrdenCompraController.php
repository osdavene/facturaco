<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraItem;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Services\DocumentoService;
use App\Services\InventarioService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenCompraController extends Controller
{
    public function __construct(
        private DocumentoService  $documentos,
        private InventarioService $inventario,
        private PdfService        $pdf,
    ) {}

    public function index(Request $request)
    {
        $ordenes = OrdenCompra::with('proveedor')
            ->when($request->buscar, function ($q) use ($request) {
                $q->where('numero',          'like', '%'.$request->buscar.'%')
                  ->orWhere('proveedor_nombre', 'like', '%'.$request->buscar.'%');
            })
            ->when($request->estado, fn ($q) => $q->where('estado', $request->estado))
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
            'proveedor_id'            => 'required|exists:proveedores,id',
            'fecha_emision'           => 'required|date',
            'fecha_esperada'          => 'nullable|date|after_or_equal:fecha_emision',
            'items'                   => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($request, $userId) {
            $proveedor   = Proveedor::findOrFail($request->proveedor_id);
            $consecutivo = OrdenCompra::siguienteConsecutivo();
            $calc        = $this->documentos->calcularItems($request->items);

            $orden = OrdenCompra::create([
                'numero'              => $consecutivo['numero'],
                'consecutivo'         => $consecutivo['consecutivo'],
                'proveedor_id'        => $proveedor->id,
                'proveedor_nombre'    => $proveedor->razon_social,
                'proveedor_documento' => $proveedor->tipo_documento.': '.$proveedor->documento_formateado,
                'fecha_emision'       => $request->fecha_emision,
                'fecha_esperada'      => $request->fecha_esperada,
                'subtotal'            => $calc['subtotal'],
                'iva'                 => $calc['iva'],
                'descuento'           => $calc['descuento'],
                'total'               => $calc['total'],
                'estado'              => $request->estado ?? 'borrador',
                'forma_pago'          => $request->forma_pago ?? 'credito',
                'plazo_pago'          => $request->plazo_pago ?? 30,
                'observaciones'       => $request->observaciones,
                'user_id'             => $userId,
            ]);

            foreach ($calc['items'] as $item) {
                OrdenCompraItem::create([
                    'orden_compra_id' => $orden->id,
                    'producto_id'     => $item['producto_id'] ?? null,
                    'codigo'          => $item['codigo']      ?? 'SIN-COD',
                    'descripcion'     => $item['descripcion'],
                    'unidad'          => $item['unidad']      ?? 'UN',
                    'cantidad'        => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'descuento_pct'   => $item['descuento_pct'],
                    'descuento'       => $item['descuento'],
                    'subtotal'        => $item['subtotal'],
                    'iva_pct'         => $item['iva_pct'],
                    'iva'             => $item['iva'],
                    'total'           => $item['total'],
                    'orden'           => $item['orden'],
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
            'estado' => 'required|in:borrador,enviada,aprobada,recibida,anulada',
        ]);

        $orden->update(['estado' => $request->estado]);

        return back()->with('success', 'Estado actualizado.');
    }

    // ── RECIBIR MERCANCÍA ─────────────────────────────────────

    public function recibir(Request $request, OrdenCompra $orden)
    {
        if ($orden->estado !== 'aprobada') {
            return back()->with('error', 'Solo puedes recibir órdenes aprobadas.');
        }

        $userId = auth()->id();

        DB::transaction(function () use ($request, $orden, $userId) {
            foreach ($orden->items as $item) {
                $cantRecibida = (float) $request->input('cantidad_'.$item->id, 0);

                if ($cantRecibida <= 0) {
                    continue;
                }

                $item->update(['cantidad_recibida' => $cantRecibida]);

                // Resolver el producto: si no está vinculado, crearlo automáticamente
                $producto = null;
                if ($item->producto_id) {
                    $producto = Producto::find($item->producto_id);
                } else {
                    $empresaId = $orden->empresa_id;
                    $codigo    = $item->codigo !== 'SIN-COD' ? $item->codigo
                        : 'OC-' . strtoupper(preg_replace('/\s+/', '-', trim($item->descripcion)));

                    $producto = Producto::create([
                        'empresa_id'     => $empresaId,
                        'codigo'         => $codigo,
                        'nombre'         => $item->descripcion,
                        'precio_compra'  => $item->precio_unitario,
                        'precio_venta'   => $item->precio_unitario,
                        'stock_actual'   => 0,
                        'stock_minimo'   => 0,
                        'es_servicio'    => false,
                        'activo'         => true,
                        'created_by'     => $userId,
                    ]);

                    $item->update(['producto_id' => $producto->id]);
                }

                if ($producto) {
                    $this->inventario->registrarEntrada(
                        $producto,
                        $cantRecibida,
                        $orden->numero,
                        $userId,
                        'Recepción OC',
                        $item->precio_unitario,
                    );
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

        return $this->pdf->stream(
            'ordenes.pdf',
            compact('orden', 'empresa'),
            'orden-'.$orden->numero.'.pdf',
        );
    }
}
