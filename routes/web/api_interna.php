<?php

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Factura;
use App\Models\Municipio;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Búsquedas AJAX internas utilizadas por formularios del sistema.
// No son parte de la API pública (routes/api.php).

Route::get('/api/departamentos', function () {
    return response()->json(
        Departamento::orderBy('nombre')->get(['id', 'nombre'])
    );
})->name('api.departamentos');

Route::get('/api/municipios', function (Request $req) {
    $query = Municipio::orderBy('nombre');
    if ($req->departamento_id) {
        $query->where('departamento_id', $req->departamento_id);
    } elseif ($req->departamento) {
        $dep = Departamento::where('nombre', strtoupper($req->departamento))->first();
        $query->where('departamento_id', $dep?->id ?? 0);
    }
    return response()->json($query->pluck('nombre'));
})->name('api.municipios');

Route::get('/api/clientes/buscar', function (Request $req) {
    $clientes = Cliente::where('activo', true)
        ->where(function ($q) use ($req) {
            $q->where('nombres',           'ilike', '%'.$req->q.'%')
              ->orWhere('apellidos',        'ilike', '%'.$req->q.'%')
              ->orWhere('razon_social',     'ilike', '%'.$req->q.'%')
              ->orWhere('numero_documento', 'ilike', '%'.$req->q.'%');
        })
        ->limit(10)
        ->get(['id','nombres','apellidos','razon_social','numero_documento',
               'tipo_documento','retefuente_pct','reteiva_pct','reteica_pct',
               'plazo_pago','email','direccion','lista_precio']);
    return response()->json($clientes);
});

Route::get('/api/proveedores/buscar', function (Request $req) {
    $proveedores = Proveedor::where('activo', true)
        ->where(function ($q) use ($req) {
            $q->where('razon_social',     'ilike', '%'.$req->q.'%')
              ->orWhere('numero_documento','ilike', '%'.$req->q.'%');
        })
        ->limit(10)
        ->get(['id','razon_social','tipo_documento','numero_documento',
               'digito_verificacion','plazo_pago','retefuente_pct']);
    return response()->json($proveedores);
});

Route::get('/api/productos/buscar', function (Request $req) {
    $lista     = $req->lista_precio ?? 'general';
    $productos = Producto::where('activo', true)
        ->where(function ($q) use ($req) {
            $q->where('nombre', 'ilike', '%'.$req->q.'%')
              ->orWhere('codigo','ilike', '%'.$req->q.'%');
        })
        ->limit(10)
        ->get(['id','codigo','nombre','precio_venta','precio_venta2','precio_venta3','iva_pct','unidad_medida_id','es_servicio','stock_actual'])
        ->map(function ($p) use ($lista) {
            $p->precio_aplicado = match ($lista) {
                'mayorista' => $p->precio_venta2 > 0 ? $p->precio_venta2 : $p->precio_venta,
                'especial'  => $p->precio_venta3 > 0 ? $p->precio_venta3 : $p->precio_venta,
                default     => $p->precio_venta,
            };
            return $p;
        });
    return response()->json($productos);
});

Route::get('/api/facturas/buscar', function (Request $req) {
    $facturas = Factura::whereIn('estado', ['emitida', 'vencida'])
        ->where(function ($q) use ($req) {
            $q->where('numero',         'ilike', '%'.$req->q.'%')
              ->orWhere('cliente_nombre','ilike', '%'.$req->q.'%');
        })
        ->limit(10)
        ->get(['id','numero','cliente_nombre','total','total_pagado'])
        ->map(function ($f) {
            $f->saldo = max(0, $f->total - $f->total_pagado);
            return $f;
        });
    return response()->json($facturas);
});
