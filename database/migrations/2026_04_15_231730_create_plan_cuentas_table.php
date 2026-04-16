<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plan_cuentas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->nullable(); // NULL = cuenta estándar PUC (compartida)
            $table->string('codigo', 10);                        // e.g. "1", "11", "1105", "110505"
            $table->string('nombre', 250);
            $table->string('tipo', 20);                          // activo|pasivo|patrimonio|ingreso|gasto|costo
            $table->string('naturaleza', 7);                     // debito|credito (saldo normal)
            $table->unsignedTinyInteger('nivel');                // 1=clase 2=grupo 3=cuenta 4=subcuenta
            $table->unsignedBigInteger('cuenta_padre_id')->nullable();
            $table->boolean('acepta_movimientos')->default(true); // solo cuentas hoja reciben asientos
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'codigo']);
            $table->foreign('cuenta_padre_id')->references('id')->on('plan_cuentas')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_cuentas');
    }
};
