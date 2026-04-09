<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique();
            $table->string('codigo_barras', 50)->nullable()->unique();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('unidad_medida_id')->nullable()->constrained('unidades_medida')->nullOnDelete();

            // Precios
            $table->decimal('precio_compra', 15, 2)->default(0);
            $table->decimal('precio_venta',  15, 2)->default(0);
            $table->decimal('precio_venta2', 15, 2)->default(0); // precio mayorista
            $table->decimal('precio_venta3', 15, 2)->default(0); // precio especial

            // IVA
            $table->decimal('iva_pct', 5, 2)->default(19);
            $table->boolean('incluye_iva')->default(false);

            // Inventario
            $table->decimal('stock_actual',  15, 4)->default(0);
            $table->decimal('stock_minimo',  15, 4)->default(0);
            $table->decimal('stock_maximo',  15, 4)->default(0);
            $table->string('ubicacion')->nullable(); // bodega o estante

            // Estado
            $table->boolean('activo')->default(true);
            $table->boolean('es_servicio')->default(false); // no maneja stock
            $table->string('imagen')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down(): void { Schema::dropIfExists('productos'); }
};