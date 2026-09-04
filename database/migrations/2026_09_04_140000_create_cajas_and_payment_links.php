<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabla de Cajas físicas / terminales
        if (!Schema::hasTable('cajas')) {
            Schema::create('cajas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->index();
                $table->string('nombre');
                $table->string('codigo')->nullable();
                $table->boolean('activa')->default(true);
                $table->timestamps();

                $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('cascade');
            });
        }

        // 2. Tabla de Turnos / Sesiones de Caja
        if (!Schema::hasTable('caja_turnos')) {
            Schema::create('caja_turnos', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->index();
                $table->unsignedBigInteger('caja_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->decimal('monto_apertura', 14, 2)->default(0);
                $table->dateTime('fecha_apertura');
                $table->decimal('monto_cierre_esperado', 14, 2)->nullable();
                $table->decimal('monto_cierre_real', 14, 2)->nullable();
                $table->decimal('diferencia', 14, 2)->default(0);
                $table->decimal('total_ventas_efectivo', 14, 2)->default(0);
                $table->decimal('total_ventas_tarjeta', 14, 2)->default(0);
                $table->decimal('total_ventas_transferencia', 14, 2)->default(0);
                $table->decimal('total_ventas_nequi', 14, 2)->default(0);
                $table->decimal('total_entradas', 14, 2)->default(0);
                $table->decimal('total_salidas', 14, 2)->default(0);
                $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
                $table->text('observaciones')->nullable();
                $table->dateTime('fecha_cierre')->nullable();
                $table->timestamps();

                $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('cascade');
                $table->foreign('caja_id')->references('id')->on('cajas')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 3. Tabla de Movimientos de Caja (Entradas y Salidas menores de efectivo)
        if (!Schema::hasTable('movimientos_caja')) {
            Schema::create('movimientos_caja', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->index();
                $table->unsignedBigInteger('caja_turno_id')->index();
                $table->enum('tipo', ['entrada', 'salida']);
                $table->decimal('monto', 14, 2);
                $table->string('motivo');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('cascade');
                $table->foreign('caja_turno_id')->references('id')->on('caja_turnos')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 4. Agregar caja_turno_id y token_pago a la tabla facturas
        Schema::table('facturas', function (Blueprint $table) {
            if (!Schema::hasColumn('facturas', 'caja_turno_id')) {
                $table->unsignedBigInteger('caja_turno_id')->nullable()->after('user_id')->index();
            }
            if (!Schema::hasColumn('facturas', 'token_pago')) {
                $table->string('token_pago', 64)->nullable()->after('observaciones')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            if (Schema::hasColumn('facturas', 'caja_turno_id')) {
                $table->dropColumn('caja_turno_id');
            }
            if (Schema::hasColumn('facturas', 'token_pago')) {
                $table->dropColumn('token_pago');
            }
        });

        Schema::dropIfExists('movimientos_caja');
        Schema::dropIfExists('caja_turnos');
        Schema::dropIfExists('cajas');
    }
};
