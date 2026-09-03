<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionPlataforma;
use App\Services\NominaService;
use Illuminate\Http\Request;

class NominaConfiguracionController extends Controller
{
    public function index()
    {
        $smmlv       = ConfiguracionPlataforma::get('smmlv', NominaService::SMMLV);
        $auxTrans    = ConfiguracionPlataforma::get('auxilio_transporte', NominaService::AUXILIO_TRANSPORTE);
        $uvt         = ConfiguracionPlataforma::get('uvt', 49799);
        $exoneracion = ConfiguracionPlataforma::get('exoneracion_parafiscales_114_1', '0');

        return view('nomina.configuracion.index', compact('smmlv', 'auxTrans', 'uvt', 'exoneracion'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'smmlv'              => 'required|numeric|min:1',
            'auxilio_transporte' => 'required|numeric|min:0',
            'uvt'                => 'required|numeric|min:1',
        ]);

        ConfiguracionPlataforma::set('smmlv', $request->smmlv);
        ConfiguracionPlataforma::set('auxilio_transporte', $request->auxilio_transporte);
        ConfiguracionPlataforma::set('uvt', $request->uvt);
        ConfiguracionPlataforma::set('exoneracion_parafiscales_114_1', $request->has('exoneracion_parafiscales_114_1') ? '1' : '0');

        return back()->with('success', 'Parámetros laborales y legales actualizados correctamente.');
    }
}
