<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas_credito', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 20)->unique();
            $table->string('prefijo', 10)->default('NC');
            $table->integer('consecutivo');

            // Factura origen
            $table->foreignId('factura_id')->constrained('facturas');
            $table->string('factura_numero', 20);

            // Cliente (snapshot)
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->string('cliente_nombre');
            $table->string('cliente_documento');

            // Tipo de devolución
            $table->enum('tipo', ['total', 'parcial'])->default('parcial');
            $table->enum('motivo', [
                'devolucion_mercancia',
                'descuento_posterior',
                'error_facturacion',
                'anulacion',
                'otro',
            ])->default('devolucion_mercancia');

            $table->text('observaciones')->nullable();
            $table->date('fecha');

            // Totales
            $table->decimal('subtotal',   15, 2)->default(0);
            $table->decimal('iva',        15, 2)->default(0);
            $table->decimal('total',      15, 2)->default(0);

            // Estado
            $table->enum('estado', ['activa', 'anulada'])->default('activa');

            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });

        Schema::create('nota_credito_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nota_credito_id')->constrained('notas_credito')->cascadeOnDelete();
            $table->foreignId('factura_item_id')->nullable()->constrained('factura_items')->nullOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('codigo');
            $table->string('descripcion');
            $table->string('unidad')->default('UN');
            $table->decimal('cantidad',        15, 4);
            $table->decimal('precio_unitario', 15, 2);
            $table->decimal('subtotal',        15, 2);
            $table->decimal('iva_pct',          5, 2)->default(0);
            $table->decimal('iva',             15, 2)->default(0);
            $table->decimal('total',           15, 2);
            $table->boolean('devolver_stock')->default(true);
            $table->integer('orden')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nota_credito_items');
        Schema::dropIfExists('notas_credito');
    }
};