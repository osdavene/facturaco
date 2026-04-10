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

        // ── Validación unificada (logo incluido) ───────────────────────────────
        // IMPORTANTE: el logo se valida aquí junto con todo lo demás para que
        // si excede el límite, Laravel devuelva el error al formulario en lugar
        // de arrojar una excepción que causa la página de error 500.
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
            // Logo: nullable para que no sea obligatorio, max:2048 = 2 MB
            'logo'                   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            // Mensajes personalizados para el logo
            'logo.image'  => 'El archivo del logo debe ser una imagen.',
            'logo.mimes'  => 'El logo debe estar en formato JPG, PNG o WEBP.',
            'logo.max'    => 'El logo no puede superar los 2 MB. Comprime la imagen antes de subirla.',
        ]);

        // ── Procesar logo ──────────────────────────────────────────────────────
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            // Borrar logo anterior si existe
            if ($empresa->logo) {
                Storage::disk('public')->delete($empresa->logo);
            }
            $data['logo'] = $request->file('logo')->store('empresa', 'public');
        } else {
            // No se subió logo nuevo: quitar el campo para no borrar el actual
            unset($data['logo']);
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