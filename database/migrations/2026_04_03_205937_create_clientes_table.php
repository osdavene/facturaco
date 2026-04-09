<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            // Datos principales
            $table->string('tipo_persona')->default('natural'); // natural, juridica
            $table->string('tipo_documento')->default('CC');    // CC, NIT, CE, PP, TI
            $table->string('numero_documento', 20)->unique();
            $table->string('digito_verificacion', 1)->nullable(); // Solo NIT
            $table->string('razon_social')->nullable();           // Si es jurídica
            $table->string('nombres')->nullable();                // Si es natural
            $table->string('apellidos')->nullable();

            // Tributario
            $table->string('regimen')->default('simple');  // simple, responsable_iva
            $table->boolean('responsable_iva')->default(false);
            $table->boolean('gran_contribuyente')->default(false);
            $table->boolean('autoretenedor')->default(false);
            $table->string('actividad_economica', 10)->nullable(); // CIIU

            // Retenciones
            $table->decimal('retefuente_pct', 5, 2)->default(0);
            $table->decimal('reteiva_pct', 5, 2)->default(0);
            $table->decimal('reteica_pct', 5, 4)->default(0);

            // Contacto
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();

            // Dirección
            $table->string('departamento')->nullable();
            $table->string('municipio')->nullable();
            $table->string('direccion')->nullable();
            $table->string('pais')->default('Colombia');

            // Comercial
            $table->integer('plazo_pago')->default(0); // días
            $table->decimal('cupo_credito', 15, 2)->default(0);
            $table->string('lista_precio')->default('general');

            // Estado
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};