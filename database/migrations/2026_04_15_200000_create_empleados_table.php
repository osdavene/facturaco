<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');

            // Datos personales
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('tipo_documento', 10)->default('CC'); // CC, CE, PA, PPT, TI
            $table->string('numero_documento', 30);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 1)->nullable(); // M, F, O
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('direccion')->nullable();

            // Datos laborales
            $table->string('cargo');
            $table->string('departamento')->nullable();
            $table->date('fecha_ingreso');
            $table->date('fecha_retiro')->nullable();
            $table->string('tipo_contrato', 30)->default('indefinido'); // indefinido, fijo, obra_labor, prestacion_servicios
            $table->string('tipo_salario', 20)->default('ordinario'); // ordinario, integral
            $table->decimal('salario_base', 14, 2);
            $table->string('periodicidad_pago', 15)->default('mensual'); // mensual, quincenal
            $table->unsignedTinyInteger('nivel_riesgo_arl')->default(1); // 1-5

            // Seguridad social
            $table->string('eps')->nullable();
            $table->string('afp')->nullable(); // fondo de pensiones
            $table->string('caja_compensacion')->nullable();

            // Datos bancarios
            $table->string('banco')->nullable();
            $table->string('tipo_cuenta', 15)->nullable(); // ahorros, corriente
            $table->string('numero_cuenta', 30)->nullable();

            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('cascade');
            $table->index(['empresa_id', 'activo']);
            $table->index(['empresa_id', 'numero_documento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
