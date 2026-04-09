<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->integer('consecutivo');

            // Proveedor
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->string('proveedor_nombre');
            $table->string('proveedor_documento');

            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_esperada')->nullable();
            $table->date('fecha_recepcion')->nullable();

            // Totales
            $table->decimal('subtotal',   15, 2)->default(0);
            $table->decimal('iva',        15, 2)->default(0);
            $table->decimal('descuento',  15, 2)->default(0);
            $table->decimal('total',      15, 2)->default(0);

            // Estado
            $table->string('estado')->default('borrador');
            // borrador, enviada, aprobada, recibida, anulada

            $table->string('forma_pago')->default('credito');
            $table->integer('plazo_pago')->default(30);
            $table->text('observaciones')->nullable();
            $table->text('notas_recepcion')->nullable();

            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('ordenes_compra'); }
};