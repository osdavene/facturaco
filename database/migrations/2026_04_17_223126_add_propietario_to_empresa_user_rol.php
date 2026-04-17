<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Reemplazar el CHECK constraint del enum para incluir 'propietario'
        DB::statement('ALTER TABLE empresa_user DROP CONSTRAINT IF EXISTS empresa_user_rol_check');
        DB::statement("ALTER TABLE empresa_user ADD CONSTRAINT empresa_user_rol_check CHECK (rol IN ('propietario', 'admin', 'operador', 'vendedor', 'bodeguero', 'contador', 'solo-lectura'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE empresa_user DROP CONSTRAINT IF EXISTS empresa_user_rol_check');
        DB::statement("ALTER TABLE empresa_user ADD CONSTRAINT empresa_user_rol_check CHECK (rol IN ('admin', 'operador'))");
    }
};
