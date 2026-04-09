<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->integer('consecutivo');

            // Cliente
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('cliente_nombre');
            $table->string('cliente_documento')->nullable();
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefono')->nullable();
            $table->string('cliente_direccion')->nullable();

            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');

            // Totales
            $table->decimal('subtotal',  15, 2)->default(0);
            $table->decimal('descuento', 15, 2)->default(0);
            $table->decimal('iva',       15, 2)->default(0);
            $table->decimal('total',     15, 2)->default(0);

            // Estado
            $table->string('estado')->default('borrador');
            // borrador, enviada, aceptada, rechazada, vencida, convertida

            $table->string('forma_pago')->default('contado');
            $table->integer('plazo_pago')->default(0);
            $table->text('observaciones')->nullable();
            $table->text('terminos')->nullable();

            // Si se convirtió en factura
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();

            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('cotizaciones'); }
};