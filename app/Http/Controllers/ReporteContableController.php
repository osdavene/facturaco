<?php

namespace App\Http\Controllers;

use App\Services\ContabilidadService;
use Illuminate\Http\Request;

class ReporteContableController extends Controller
{
    public function __construct(private ContabilidadService $contabilidad) {}

    public function balance(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $hasta     = $request->hasta ?? now()->toDateString();

        $data = $this->contabilidad->balance($empresaId, $hasta);

        $totalActivo     = array_sum(array_column($data['activo'],     'saldo'));
        $totalPasivo     = array_sum(array_column($data['pasivo'],     'saldo'));
        $totalPatrimonio = array_sum(array_column($data['patrimonio'], 'saldo'));

        return view('contabilidad.reportes.balance', array_merge($data, [
            'hasta'           => $hasta,
            'totalActivo'     => $totalActivo,
            'totalPasivo'     => $totalPasivo,
            'totalPatrimonio' => $totalPatrimonio,
        ]));
    }

    public function pyg(Request $request)
    {
        $empresaId = session('empresa_activa_id');
        $desde     = $request->desde ?? now()->startOfYear()->toDateString();
        $hasta     = $request->hasta ?? now()->toDateString();

        $data = $this->contabilidad->estadoResultados($empresaId, $desde, $hasta);

        return view('contabilidad.reportes.pyg', array_merge($data, [
            'desde' => $desde,
            'hasta' => $hasta,
        ]));
    }
}
