<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nominas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');

            $table->string('nombre'); // ej: "Nómina Mayo 2025"
            $table->date('periodo_inicio');
            $table->date('periodo_fin');
            $table->date('fecha_pago')->nullable();
            $table->string('periodicidad', 15)->default('mensual'); // mensual, quincenal

            $table->string('estado', 20)->default('borrador'); // borrador, procesada, pagada, anulada

            // Totales generales
            $table->decimal('total_devengado', 14, 2)->default(0);
            $table->decimal('total_deducciones', 14, 2)->default(0);
            $table->decimal('total_neto', 14, 2)->default(0);
            $table->decimal('total_aportes_empleador', 14, 2)->default(0);

            $table->text('observaciones')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'periodo_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominas');
    }
};
