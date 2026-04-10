<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tablas = ['clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida'];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tablas = ['clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida'];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropForeign([$tabla . '_created_by_foreign']);
                $table->dropForeign([$tabla . '_updated_by_foreign']);
                $table->dropColumn(['created_by', 'updated_by']);
            });
        }
    }
};