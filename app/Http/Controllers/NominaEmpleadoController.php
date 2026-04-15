<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;

class NominaEmpleadoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');
        $estado = $request->get('estado', ''); // activo, inactivo

        $empleados = Empleado::query()
            ->when($buscar, fn($q) => $q
                ->where('nombres', 'like', "%$buscar%")
                ->orWhere('apellidos', 'like', "%$buscar%")
                ->orWhere('numero_documento', 'like', "%$buscar%")
                ->orWhere('cargo', 'like', "%$buscar%")
            )
            ->when($estado === 'activo',   fn($q) => $q->where('activo', true))
            ->when($estado === 'inactivo', fn($q) => $q->where('activo', false))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(20)
            ->withQueryString();

        return view('nomina.empleados.index', compact('empleados', 'buscar', 'estado'));
    }

    public function create()
    {
        return view('nomina.empleados.form', ['empleado' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validar($request);

        Empleado::create($data);

        return redirect()->route('nomina.empleados.index')
            ->with('success', 'Empleado registrado correctamente.');
    }

    public function edit(Empleado $empleado)
    {
        return view('nomina.empleados.form', compact('empleado'));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $data = $this->validar($request, $empleado);

        $empleado->update($data);

        return redirect()->route('nomina.empleados.index')
            ->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Empleado $empleado)
    {
        // Si tiene liquidaciones, solo desactivar — nunca borrar
        $empleado->update(['activo' => false]);
        $empleado->delete(); // SoftDelete

        return redirect()->route('nomina.empleados.index')
            ->with('success', 'Empleado archivado. El registro se conserva para trazabilidad.');
    }

    public function toggleActivo(Empleado $empleado)
    {
        $empleado->update(['activo' => !$empleado->activo]);

        $msg = $empleado->activo ? 'Empleado activado.' : 'Empleado desactivado.';

        return back()->with('success', $msg);
    }

    // ── Privado ───────────────────────────────────────────────────

    private function validar(Request $request, ?Empleado $empleado = null): array
    {
        return $request->validate([
            'nombres'            => 'required|string|max:100',
            'apellidos'          => 'required|string|max:100',
            'tipo_documento'     => 'required|in:CC,CE,PA,PPT,TI',
            'numero_documento'   => 'required|string|max:30',
            'fecha_nacimiento'   => 'nullable|date|before:today',
            'sexo'               => 'nullable|in:M,F,O',
            'email'              => 'nullable|email|max:150',
            'telefono'           => 'nullable|string|max:20',
            'direccion'          => 'nullable|string|max:200',
            'cargo'              => 'required|string|max:100',
            'departamento'       => 'nullable|string|max:80',
            'fecha_ingreso'      => 'required|date',
            'fecha_retiro'       => 'nullable|date|after:fecha_ingreso',
            'tipo_contrato'      => 'required|in:indefinido,fijo,obra_labor,prestacion_servicios',
            'tipo_salario'       => 'required|in:ordinario,integral',
            'salario_base'       => 'required|numeric|min:1',
            'periodicidad_pago'  => 'required|in:mensual,quincenal',
            'nivel_riesgo_arl'   => 'required|integer|between:1,5',
            'eps'                => 'nullable|string|max:100',
            'afp'                => 'nullable|string|max:100',
            'caja_compensacion'  => 'nullable|string|max:100',
            'banco'              => 'nullable|string|max:80',
            'tipo_cuenta'        => 'nullable|in:ahorros,corriente',
            'numero_cuenta'      => 'nullable|string|max:30',
            'activo'             => 'boolean',
            'observaciones'      => 'nullable|string|max:500',
        ]);
    }
}
