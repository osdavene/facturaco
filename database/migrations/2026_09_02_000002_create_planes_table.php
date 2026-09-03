<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 12, 2)->default(0);
            $table->integer('limite_facturas_mes')->nullable(); // null = ilimitadas
            $table->integer('limite_usuarios')->nullable();      // null = ilimitados
            $table->integer('limite_productos')->nullable();     // null = ilimitados
            $table->boolean('soporta_dian')->default(true);
            $table->boolean('soporta_pos')->default(true);
            $table->boolean('soporta_nomina')->default(true);
            $table->boolean('soporta_contabilidad')->default(true);
            $table->string('color', 20)->default('amber');
            $table->boolean('destacado')->default(false);
            $table->boolean('activo')->default(true);
            $table->integer('orden')->default(0);
            $table->timestamps();
        });

        Schema::table('empresa', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->date('plan_vencimiento')->nullable();
            $table->integer('plan_facturas_adicionales')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'plan_vencimiento', 'plan_facturas_adicionales']);
        });

        Schema::dropIfExists('planes');
    }
};
