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
        // ── Asientos (cabecera) ──────────────────────────────────
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('numero', 20);               // AC-2025-00001
            $table->date('fecha');
            $table->string('descripcion', 500);
            $table->string('tipo', 30)->default('manual'); // factura|recibo|compra|nomina|manual
            $table->string('referencia_tipo', 50)->nullable(); // Factura|ReciboCaja|OrdenCompra…
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('estado', 15)->default('confirmado'); // confirmado|anulado
            $table->decimal('total_debito',  15, 2)->default(0);
            $table->decimal('total_credito', 15, 2)->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'numero']);
            $table->foreign('empresa_id')->references('id')->on('empresa')->cascadeOnDelete();
        });

        // ── Líneas de asiento ────────────────────────────────────
        Schema::create('asiento_lineas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asiento_id');
            $table->unsignedBigInteger('cuenta_id');
            $table->string('descripcion', 300)->nullable();
            $table->decimal('debito',  15, 2)->default(0);
            $table->decimal('credito', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('asiento_id')->references('id')->on('asientos_contables')->cascadeOnDelete();
            $table->foreign('cuenta_id')->references('id')->on('plan_cuentas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asiento_lineas');
        Schema::dropIfExists('asientos_contables');
    }
};
