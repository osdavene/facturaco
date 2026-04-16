<?php

namespace App\Http\Controllers;

use App\Models\PlanCuenta;
use Illuminate\Http\Request;

class PlanCuentasController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = session('empresa_activa_id');

        $cuentas = PlanCuenta::deEmpresa($empresaId)
            ->when($request->buscar, fn($q) =>
                $q->where('codigo', 'like', $request->buscar . '%')
                  ->orWhere('nombre', 'ilike', '%' . $request->buscar . '%')
            )
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->nivel, fn($q) => $q->where('nivel', $request->nivel))
            ->orderBy('codigo')
            ->paginate(50)
            ->withQueryString();

        return view('contabilidad.plan-cuentas.index', compact('cuentas'));
    }

    public function create()
    {
        $empresaId = session('empresa_activa_id');
        $padres = PlanCuenta::deEmpresa($empresaId)
            ->activas()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'nivel']);

        return view('contabilidad.plan-cuentas.form', compact('padres'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'              => 'required|string|max:10',
            'nombre'              => 'required|string|max:250',
            'tipo'                => 'required|in:activo,pasivo,patrimonio,ingreso,gasto,costo',
            'naturaleza'          => 'required|in:debito,credito',
            'nivel'               => 'required|integer|min:1|max:4',
            'cuenta_padre_id'     => 'nullable|exists:plan_cuentas,id',
            'acepta_movimientos'  => 'boolean',
        ]);

        $data['empresa_id']         = session('empresa_activa_id');
        $data['acepta_movimientos'] = $request->boolean('acepta_movimientos', true);
        $data['activo']             = true;

        // Verificar unicidad dentro de la empresa
        $existe = PlanCuenta::where('empresa_id', $data['empresa_id'])
            ->where('codigo', $data['codigo'])
            ->exists();
        if ($existe) {
            return back()->withErrors(['codigo' => 'Ya existe una cuenta con ese código.'])->withInput();
        }

        PlanCuenta::create($data);

        return redirect()->route('contabilidad.plan-cuentas.index')
            ->with('success', 'Cuenta ' . $data['codigo'] . ' creada correctamente.');
    }

    public function edit(PlanCuenta $planCuenta)
    {
        $empresaId = session('empresa_activa_id');
        $padres = PlanCuenta::deEmpresa($empresaId)
            ->where('id', '!=', $planCuenta->id)
            ->activas()
            ->orderBy('codigo')
            ->get(['id', 'codigo', 'nombre', 'nivel']);

        return view('contabilidad.plan-cuentas.form', ['cuenta' => $planCuenta, 'padres' => $padres]);
    }

    public function update(Request $request, PlanCuenta $planCuenta)
    {
        // Solo se pueden editar cuentas propias de la empresa (no las estándar)
        if ($planCuenta->empresa_id === null) {
            return back()->with('error', 'Las cuentas estándar del PUC no se pueden modificar.');
        }

        $data = $request->validate([
            'nombre'              => 'required|string|max:250',
            'tipo'                => 'required|in:activo,pasivo,patrimonio,ingreso,gasto,costo',
            'naturaleza'          => 'required|in:debito,credito',
            'cuenta_padre_id'     => 'nullable|exists:plan_cuentas,id',
            'acepta_movimientos'  => 'boolean',
            'activo'              => 'boolean',
        ]);

        $data['acepta_movimientos'] = $request->boolean('acepta_movimientos');
        $data['activo']             = $request->boolean('activo', true);

        $planCuenta->update($data);

        return redirect()->route('contabilidad.plan-cuentas.index')
            ->with('success', 'Cuenta actualizada.');
    }
}
