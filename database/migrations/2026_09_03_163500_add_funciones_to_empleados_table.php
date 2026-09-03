<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->text('funciones')->nullable()->after('departamento');
            $table->string('horario', 150)->nullable()->after('funciones');
            $table->string('jefe_inmediato', 150)->nullable()->after('horario');
            $table->text('habilidades_requisitos')->nullable()->after('jefe_inmediato');
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn(['funciones', 'horario', 'jefe_inmediato', 'habilidades_requisitos']);
        });
    }
};
