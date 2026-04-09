<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('tipo'); // entrada, salida, ajuste
            $table->decimal('cantidad', 15, 4);
            $table->decimal('stock_anterior', 15, 4);
            $table->decimal('stock_nuevo',    15, 4);
            $table->decimal('costo_unitario', 15, 2)->default(0);
            $table->string('motivo')->nullable();      // compra, venta, ajuste...
            $table->string('referencia')->nullable();   // N° factura, OC, etc
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('movimientos_inventario'); }
};