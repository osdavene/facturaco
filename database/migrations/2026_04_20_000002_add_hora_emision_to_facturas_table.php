<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            // Hora de emisión fija al momento de crear la factura.
            // Necesaria para CUFE: debe ser siempre la misma, nunca now() en recálculo.
            $table->time('hora_emision')->nullable()->after('fecha_emision');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropColumn('hora_emision');
        });
    }
};
