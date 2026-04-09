<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('nit', 20);
            $table->string('digito_verificacion', 1)->nullable();
            $table->string('tipo_persona')->default('juridica');
            $table->string('regimen')->default('responsable_iva');

            // Contacto
            $table->string('email')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('sitio_web')->nullable();

            // Dirección
            $table->string('pais')->default('Colombia');
            $table->string('departamento')->nullable();
            $table->string('municipio')->nullable();
            $table->string('direccion')->nullable();

            // DIAN
            $table->string('prefijo_factura', 10)->default('FE');
            $table->integer('resolucion_numero')->nullable();
            $table->date('resolucion_fecha')->nullable();
            $table->date('resolucion_vencimiento')->nullable();
            $table->integer('consecutivo_desde')->default(1);
            $table->integer('consecutivo_hasta')->default(99999);
            $table->integer('consecutivo_actual')->default(0);
            $table->string('clave_tecnica')->nullable();
            $table->boolean('factura_electronica')->default(false);

            // Configuración
            $table->string('logo')->nullable();
            $table->string('moneda')->default('COP');
            $table->integer('decimales')->default(0);
            $table->text('pie_factura')->nullable();
            $table->text('terminos_condiciones')->nullable();

            // Impuestos por defecto
            $table->decimal('iva_defecto', 5, 2)->default(19);
            $table->decimal('retefuente_defecto', 5, 2)->default(0);
            $table->decimal('reteica_defecto', 5, 4)->default(0);

            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('empresa'); }
};