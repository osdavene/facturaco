<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento')->default('NIT');
            $table->string('numero_documento', 20)->unique();
            $table->string('digito_verificacion', 1)->nullable();
            $table->string('razon_social');
            $table->string('nombre_contacto')->nullable();
            $table->string('cargo_contacto')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('departamento')->nullable();
            $table->string('municipio')->nullable();
            $table->string('direccion')->nullable();
            $table->string('regimen')->default('responsable_iva');
            $table->boolean('gran_contribuyente')->default(false);
            $table->boolean('autoretenedor')->default(false);
            $table->decimal('retefuente_pct', 5, 2)->default(0);
            $table->decimal('reteiva_pct', 5, 2)->default(0);
            $table->decimal('reteica_pct', 5, 4)->default(0);
            $table->integer('plazo_pago')->default(30);
            $table->string('cuenta_bancaria')->nullable();
            $table->string('banco')->nullable();
            $table->string('tipo_cuenta')->nullable();
            $table->decimal('cupo_credito', 15, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};