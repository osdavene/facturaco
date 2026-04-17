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
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2)->unique();
            $table->string('nombre', 100)->unique();
        });

        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos')->cascadeOnDelete();
            $table->string('codigo', 5)->unique();
            $table->string('nombre', 120);
            $table->index(['departamento_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
        Schema::dropIfExists('departamentos');
    }
};
