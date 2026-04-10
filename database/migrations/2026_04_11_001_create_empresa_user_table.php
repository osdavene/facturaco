<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('rol', ['admin', 'operador'])->default('operador');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'user_id']);
        });

        // Vincular todos los usuarios existentes a la empresa 1 como admin
        $empresa = DB::table('empresa')->first();
        if ($empresa) {
            $users = DB::table('users')->pluck('id');
            foreach ($users as $userId) {
                DB::table('empresa_user')->insertOrIgnore([
                    'empresa_id' => $empresa->id,
                    'user_id'    => $userId,
                    'rol'        => 'admin',
                    'activo'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa_user');
    }
};
