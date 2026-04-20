<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dian_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->foreignId('empresa_id')->constrained('empresa')->cascadeOnDelete();

            // Tipo de evento
            $table->string('tipo'); // envio | consulta | acuse_recibo | aceptacion | rechazo_comprador | rechazo_dian

            // Estado del intento
            $table->string('estado')->default('pendiente'); // pendiente | procesando | exitoso | fallido

            // Datos de respuesta DIAN
            $table->string('cufe', 200)->nullable();
            $table->string('codigo_respuesta', 10)->nullable();
            $table->text('descripcion')->nullable();
            $table->json('errores')->nullable();

            // Payload completo para debugging
            $table->json('payload')->nullable();

            // Para eventos de compradores
            $table->string('actor_nombre')->nullable(); // quien emitió el evento
            $table->string('actor_documento')->nullable();
            $table->text('nota')->nullable();

            $table->timestamps();

            $table->index(['factura_id', 'tipo']);
            $table->index(['empresa_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dian_eventos');
    }
};
