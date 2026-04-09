<?php
namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresa = Empresa::obtener();
        return view('empresa.index', compact('empresa'));
    }

    public function update(Request $request)
    {
        $empresa = Empresa::obtener();

        $data = $request->validate([
            'razon_social'           => 'required|string|max:255',
            'nombre_comercial'       => 'nullable|string|max:255',
            'nit'                    => 'required|string|max:20',
            'digito_verificacion'    => 'nullable|string|max:1',
            'tipo_persona'           => 'required|in:natural,juridica',
            'regimen'                => 'required|in:simple,responsable_iva',
            'email'                  => 'nullable|email',
            'telefono'               => 'nullable|string|max:20',
            'celular'                => 'nullable|string|max:20',
            'sitio_web'              => 'nullable|string|max:255',
            'departamento'           => 'nullable|string|max:100',
            'municipio'              => 'nullable|string|max:100',
            'direccion'              => 'nullable|string|max:255',
            'prefijo_factura'        => 'required|string|max:10',
            'resolucion_numero'      => 'nullable|integer',
            'resolucion_fecha'       => 'nullable|date',
            'resolucion_vencimiento' => 'nullable|date',
            'consecutivo_desde'      => 'nullable|integer|min:1',
            'consecutivo_hasta'      => 'nullable|integer|min:1',
            'clave_tecnica'          => 'nullable|string',
            'factura_electronica'    => 'boolean',
            'pie_factura'            => 'nullable|string',
            'terminos_condiciones'   => 'nullable|string',
            'iva_defecto'            => 'numeric|min:0|max:100',
            'retefuente_defecto'     => 'numeric|min:0|max:100',
            'reteica_defecto'        => 'numeric|min:0|max:100',
        ]);

        // Logo
        if ($request->hasFile('logo')) {
            $request->validate(['logo' => 'image|max:2048']);
            if ($empresa->logo) {
                Storage::disk('public')->delete($empresa->logo);
            }
            $data['logo'] = $request->file('logo')->store('empresa', 'public');
        }

        $data['factura_electronica'] = $request->boolean('factura_electronica');

        $empresa->update($data);

        return redirect()->route('empresa.index')
            ->with('success', 'Configuración guardada correctamente.');
    }

    public function deleteLogo()
    {
        $empresa = Empresa::obtener();
        if ($empresa->logo) {
            Storage::disk('public')->delete($empresa->logo);
            $empresa->update(['logo' => null]);
        }
        return back()->with('success', 'Logo eliminado.');
    }
}