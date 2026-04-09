<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();

            // Numeración
            $table->string('numero', 20)->unique();       // FE-2026-0001
            $table->string('prefijo', 10)->default('FE');
            $table->integer('consecutivo');

            // Tipo
            $table->string('tipo')->default('factura');   // factura, cotizacion, remision

            // Cliente
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('cliente_nombre');             // snapshot del cliente
            $table->string('cliente_documento');
            $table->string('cliente_direccion')->nullable();
            $table->string('cliente_email')->nullable();

            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');

            // Totales
            $table->decimal('subtotal',      15, 2)->default(0);
            $table->decimal('descuento',     15, 2)->default(0);
            $table->decimal('base_iva',      15, 2)->default(0);
            $table->decimal('iva',           15, 2)->default(0);
            $table->decimal('retefuente',    15, 2)->default(0);
            $table->decimal('reteiva',       15, 2)->default(0);
            $table->decimal('reteica',       15, 2)->default(0);
            $table->decimal('total',         15, 2)->default(0);
            $table->decimal('total_pagado',  15, 2)->default(0);

            // Estado
            $table->string('estado')->default('borrador'); // borrador, emitida, pagada, vencida, anulada

            // Info adicional
            $table->text('observaciones')->nullable();
            $table->string('forma_pago')->default('contado'); // contado, credito
            $table->integer('plazo_pago')->default(0);

            // DIAN
            $table->string('cufe')->nullable();           // código único DIAN
            $table->boolean('enviada_dian')->default(false);
            $table->timestamp('fecha_dian')->nullable();

            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('facturas'); }
};