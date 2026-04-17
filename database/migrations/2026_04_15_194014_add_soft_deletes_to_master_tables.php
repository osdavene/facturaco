<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tablas = ['clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida', 'empleados'];

        foreach ($tablas as $tabla) {
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'deleted_at')) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->softDeletes();
                });
            }
        }
    }

    public function down(): void
    {
        $tablas = ['clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida', 'empleados'];

        foreach ($tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
