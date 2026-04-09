<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('recibos_caja', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->integer('consecutivo');

            // Cliente
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('cliente_nombre');
            $table->string('cliente_documento');

            // Factura relacionada (opcional)
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();

            // Pago
            $table->date('fecha');
            $table->decimal('valor', 15, 2);
            $table->string('forma_pago')->default('efectivo');
            $table->string('banco')->nullable();
            $table->string('num_referencia')->nullable();
            $table->text('concepto')->nullable();
            $table->text('observaciones')->nullable();

            // Estado
            $table->string('estado')->default('activo'); // activo, anulado

            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('recibos_caja'); }
};