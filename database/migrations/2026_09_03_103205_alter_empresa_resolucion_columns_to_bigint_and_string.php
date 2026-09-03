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
        Schema::table('empresa', function (Blueprint $table) {
            $table->string('resolucion_numero', 50)->nullable()->change();
            $table->bigInteger('consecutivo_desde')->default(1)->change();
            $table->bigInteger('consecutivo_hasta')->default(99999)->change();
            $table->bigInteger('consecutivo_actual')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresa', function (Blueprint $table) {
            $table->integer('resolucion_numero')->nullable()->change();
            $table->integer('consecutivo_desde')->default(1)->change();
            $table->integer('consecutivo_hasta')->default(99999)->change();
            $table->integer('consecutivo_actual')->default(0)->change();
        });
    }
};
