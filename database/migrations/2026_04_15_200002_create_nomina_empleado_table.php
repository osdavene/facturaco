<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomina_empleado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nomina_id');
            $table->unsignedBigInteger('empleado_id');

            // ── Tiempo trabajado ──────────────────────────
            $table->decimal('dias_trabajados', 5, 2)->default(30);
            $table->decimal('dias_vacaciones', 5, 2)->default(0);
            $table->decimal('dias_incapacidad', 5, 2)->default(0);
            $table->decimal('dias_licencia_remunerada', 5, 2)->default(0);
            $table->decimal('horas_extras_diurnas', 6, 2)->default(0);       // HED (+25%)
            $table->decimal('horas_extras_nocturnas', 6, 2)->default(0);     // HEN (+75%)
            $table->decimal('horas_extras_fest_diurnas', 6, 2)->default(0);  // HEFD (+100%)
            $table->decimal('horas_extras_fest_nocturnas', 6, 2)->default(0);// HEFN (+150%)
            $table->decimal('horas_recargo_nocturno', 6, 2)->default(0);     // RN (+35%)

            // ── DEVENGADOS ───────────────────────────────
            $table->decimal('salario_basico', 14, 2)->default(0);
            $table->decimal('auxilio_transporte', 14, 2)->default(0);
            $table->decimal('valor_horas_extras', 14, 2)->default(0);
            $table->decimal('comisiones', 14, 2)->default(0);
            $table->decimal('bonificaciones', 14, 2)->default(0);
            $table->decimal('otros_devengados', 14, 2)->default(0);
            $table->decimal('total_devengado', 14, 2)->default(0);

            // ── DEDUCCIONES EMPLEADO ─────────────────────
            $table->decimal('ibc', 14, 2)->default(0); // Ingreso Base de Cotización
            $table->decimal('deduccion_salud', 14, 2)->default(0);      // 4% IBC
            $table->decimal('deduccion_pension', 14, 2)->default(0);    // 4% IBC
            $table->decimal('fondo_solidaridad', 14, 2)->default(0);    // 1% si IBC > 4 SMMLV
            $table->decimal('retencion_fuente', 14, 2)->default(0);
            $table->decimal('otras_deducciones', 14, 2)->default(0);
            $table->decimal('total_deducciones', 14, 2)->default(0);

            // ── NETO ─────────────────────────────────────
            $table->decimal('neto_pagar', 14, 2)->default(0);

            // ── APORTES EMPLEADOR (informativo) ──────────
            $table->decimal('aporte_salud_empleador', 14, 2)->default(0);    // 8.5%
            $table->decimal('aporte_pension_empleador', 14, 2)->default(0);  // 12%
            $table->decimal('aporte_arl', 14, 2)->default(0);
            $table->decimal('aporte_caja_compensacion', 14, 2)->default(0);  // 4%
            $table->decimal('aporte_sena', 14, 2)->default(0);               // 2%
            $table->decimal('aporte_icbf', 14, 2)->default(0);               // 3%
            $table->decimal('total_aportes_empleador', 14, 2)->default(0);

            // ── ACUMULADOS PRESTACIONES (en este período) ─
            $table->decimal('acumulado_cesantias', 14, 2)->default(0);
            $table->decimal('acumulado_intereses_cesantias', 14, 2)->default(0);
            $table->decimal('acumulado_prima', 14, 2)->default(0);
            $table->decimal('acumulado_vacaciones', 14, 2)->default(0);

            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('nomina_id')->references('id')->on('nominas')->onDelete('cascade');
            $table->foreign('empleado_id')->references('id')->on('empleados')->onDelete('cascade');
            $table->unique(['nomina_id', 'empleado_id']);
            $table->index('empleado_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomina_empleado');
    }
};
