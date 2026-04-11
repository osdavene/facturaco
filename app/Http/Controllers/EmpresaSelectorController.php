<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmpresaSelectorController extends Controller
{
    /** Muestra el selector cuando el usuario pertenece a varias empresas */
    public function index()
    {
        $empresas = auth()->user()
            ->empresas()
            ->wherePivot('activo', true)
            ->get();

        // Si solo tiene una, seleccionar automáticamente
        if ($empresas->count() === 1) {
            return $this->elegir($empresas->first()->id);
        }

        return view('empresas.seleccionar', compact('empresas'));
    }

    /** POST: establece la empresa activa en sesión */
    public function elegir(int $empresaId)
    {
        $empresa = auth()->user()
            ->empresas()
            ->wherePivot('activo', true)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        $this->establecerSesionEmpresa($empresa);

        return redirect()->intended(route('dashboard'));
    }

    /** Formulario para crear nueva empresa */
    public function crear()
    {
        return view('empresas.crear');
    }

    /** Guarda la nueva empresa y vincula al usuario actual como admin */
    public function store(Request $request)
    {
        $request->validate([
            'razon_social' => 'required|string|max:200',
            'nit'          => 'required|string|max:20',
            'moneda'       => 'required|string|max:5',
        ], [
            'razon_social.required' => 'La razón social es obligatoria.',
            'nit.required'          => 'El NIT es obligatorio.',
        ]);

        $empresa = Empresa::create([
            'razon_social'    => strtoupper($request->razon_social),
            'nombre_comercial'=> $request->nombre_comercial,
            'nit'             => $request->nit,
            'digito_verificacion' => $request->digito_verificacion,
            'email'           => $request->email,
            'telefono'        => $request->telefono,
            'municipio'       => $request->municipio,
            'departamento'    => $request->departamento,
            'direccion'       => $request->direccion,
            'prefijo_factura' => strtoupper($request->prefijo_factura ?? 'FE'),
            'moneda'          => $request->moneda ?? 'COP',
            'iva_defecto'     => 19,
        ]);

        // Vincular al usuario creador como admin
        auth()->user()->empresas()->attach($empresa->id, [
            'rol'    => 'admin',
            'activo' => true,
        ]);

        $this->establecerSesionEmpresa($empresa);

        return redirect()->route('dashboard')
            ->with('success', "Empresa «{$empresa->razon_social}» creada correctamente.");
    }

    /**
     * Almacena en sesión el ID activo, el ID de la raíz del grupo
     * y todos los IDs del grupo (para scoping de catálogos compartidos).
     */
    public static function establecerSesionEmpresa(Empresa $empresa): void
    {
        $raiz      = $empresa->raiz();
        $grupoIds  = $empresa->idsGrupo();

        session([
            'empresa_activa_id' => $empresa->id,
            'empresa_raiz_id'   => $raiz->id,
            'empresa_grupo_ids' => $grupoIds,
        ]);
    }
}
