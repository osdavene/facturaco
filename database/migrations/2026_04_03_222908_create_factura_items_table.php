<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('factura_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();

            // Snapshot del producto al momento de facturar
            $table->string('codigo');
            $table->string('descripcion');
            $table->string('unidad')->default('UN');

            // Cantidades y precios
            $table->decimal('cantidad',        15, 4);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('descuento_pct',   5,  2)->default(0);
            $table->decimal('descuento',       15, 2)->default(0);
            $table->decimal('subtotal',        15, 2);

            // Impuestos por línea
            $table->decimal('iva_pct',      5, 2)->default(19);
            $table->decimal('iva',         15, 2)->default(0);
            $table->decimal('total',       15, 2);

            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('factura_items'); }
};