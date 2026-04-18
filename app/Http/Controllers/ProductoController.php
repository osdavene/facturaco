<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Categoria;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $soloArchivados = $request->estado === 'archivado';

        $query = $soloArchivados
            ? Producto::onlyTrashed()->with(['categoria', 'unidadMedida'])
            : Producto::with(['categoria', 'unidadMedida']);

        $productos = $query
            ->when($request->buscar, fn($q) => $q->buscar($request->buscar))
            ->when($request->categoria_id, fn($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->estado === 'activo',   fn($q) => $q->where('activo', true))
            ->when($request->estado === 'inactivo', fn($q) => $q->where('activo', false))
            ->when($request->stock === 'bajo',      fn($q) => $q->whereColumn('stock_actual', '<=', 'stock_minimo')->where('es_servicio', false))
            ->orderBy('nombre')
            ->paginate(20)
            ->withQueryString();

        $categorias     = Categoria::where('activo', true)->orderBy('nombre')->get();
        $totalProductos = Producto::count();
        $bajoStock      = Producto::whereColumn('stock_actual', '<=', 'stock_minimo')->where('es_servicio', false)->count();
        $sinStock       = Producto::where('stock_actual', 0)->where('es_servicio', false)->count();
        $archivados     = Producto::onlyTrashed()->count();

        return view('inventario.index', compact(
            'productos', 'categorias',
            'totalProductos', 'bajoStock', 'sinStock', 'archivados', 'soloArchivados'
        ));
    }

    public function create()
    {
        $categorias   = Categoria::where('activo', true)->orderBy('nombre')->get();
        $unidades     = UnidadMedida::where('activo', true)->orderBy('nombre')->get();
        return view('inventario.create', compact('categorias', 'unidades'));
    }

    public function store(StoreProductoRequest $request)
    {
        $data = $request->validated();

        $data['incluye_iva'] = $request->boolean('incluye_iva');
        $data['es_servicio'] = $request->boolean('es_servicio');

        DB::transaction(function() use ($data, $request) {
            $producto = Producto::create($data);

            // Registrar movimiento inicial si tiene stock
            if ($producto->stock_actual > 0) {
                MovimientoInventario::create([
                    'producto_id'    => $producto->id,
                    'tipo'           => 'entrada',
                    'cantidad'       => $producto->stock_actual,
                    'stock_anterior' => 0,
                    'stock_nuevo'    => $producto->stock_actual,
                    'costo_unitario' => $producto->precio_compra,
                    'motivo'         => 'Stock inicial',
                    'referencia'     => 'INICIO',
                    'user_id'        => auth()->id(),
                ]);
            }
        });

        return redirect()->route('inventario.index')
            ->with('success', 'Producto creado correctamente.');
    }

    public function show(Producto $inventario)
    {
        $inventario->load(['categoria', 'unidadMedida']);
        $movimientos = MovimientoInventario::where('producto_id', $inventario->id)
            ->with('usuario')
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return view('inventario.show', compact('inventario', 'movimientos'));
    }

    public function edit(Producto $inventario)
    {
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();
        $unidades   = UnidadMedida::where('activo', true)->orderBy('nombre')->get();
        return view('inventario.edit', compact('inventario', 'categorias', 'unidades'));
    }

    public function update(UpdateProductoRequest $request, Producto $inventario)
    {
        $data = $request->validated();

        $data['incluye_iva'] = $request->boolean('incluye_iva');
        $data['es_servicio'] = $request->boolean('es_servicio');
        $data['activo']      = $request->boolean('activo');

        $inventario->update($data);

        return redirect()->route('inventario.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $inventario)
    {
        $inventario->update(['activo' => false]);
        $inventario->delete(); // SoftDelete
        return redirect()->route('inventario.index')
            ->with('success', 'Producto archivado. El registro se conserva para trazabilidad.');
    }

    public function restore(int $id)
    {
        $inventario = Producto::onlyTrashed()->findOrFail($id);
        $inventario->restore();
        $inventario->update(['activo' => true]);

        return redirect()->route('inventario.edit', $inventario)
            ->with('success', 'Producto restaurado. Completa los datos faltantes.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionó ningún elemento.');
        }
        $count = Producto::whereIn('id', $ids)->count();
        Producto::whereIn('id', $ids)->update(['activo' => false]);
        Producto::whereIn('id', $ids)->delete(); // SoftDelete
        return redirect()->route('inventario.index')
            ->with('success', "{$count} producto(s) archivado(s) correctamente.");
    }

    // Ajuste de stock
    public function ajustarStock(Request $request, Producto $inventario)
    {
        $request->validate([
            'tipo'     => 'required|in:entrada,salida,ajuste',
            'cantidad' => 'required|numeric|min:0.0001',
            'motivo'   => 'required|string|max:255',
        ]);

        DB::transaction(function() use ($request, $inventario) {
            $stockAnterior = $inventario->stock_actual;

            if ($request->tipo === 'entrada') {
                $stockNuevo = $stockAnterior + $request->cantidad;
            } elseif ($request->tipo === 'salida') {
                $stockNuevo = max(0, $stockAnterior - $request->cantidad);
            } else {
                $stockNuevo = $request->cantidad; // ajuste directo
            }

            $inventario->update(['stock_actual' => $stockNuevo]);

            MovimientoInventario::create([
                'producto_id'    => $inventario->id,
                'tipo'           => $request->tipo,
                'cantidad'       => $request->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo'    => $stockNuevo,
                'costo_unitario' => $inventario->precio_compra,
                'motivo'         => $request->motivo,
                'referencia'     => $request->referencia ?? '',
                'user_id'        => auth()->id(),
            ]);
        });

        return redirect()->route('inventario.show', $inventario)
            ->with('success', 'Stock ajustado correctamente.');
    }
}