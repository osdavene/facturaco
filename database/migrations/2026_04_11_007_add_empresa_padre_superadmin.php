<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega empresa_padre_id a empresa (jerarquía matriz/filial)
     * y is_superadmin a users (administrador de plataforma).
     * Usa IF NOT EXISTS para ser idempotente.
     */
    public function up(): void
    {
        // ── empresa: columna empresa_padre_id ─────────────────────────────
        DB::statement("
            ALTER TABLE \"empresa\"
            ADD COLUMN IF NOT EXISTS \"empresa_padre_id\" BIGINT NULL
            REFERENCES \"empresa\"(\"id\") ON DELETE SET NULL
        ");

        // Índice para acelerar consultas de filiales
        DB::statement("
            CREATE INDEX IF NOT EXISTS \"empresa_empresa_padre_id_index\"
            ON \"empresa\" (\"empresa_padre_id\")
        ");

        // ── users: columna is_superadmin ──────────────────────────────────
        DB::statement("
            ALTER TABLE \"users\"
            ADD COLUMN IF NOT EXISTS \"is_superadmin\" BOOLEAN NOT NULL DEFAULT FALSE
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS \"empresa_empresa_padre_id_index\"");
        DB::statement("ALTER TABLE \"empresa\" DROP COLUMN IF EXISTS \"empresa_padre_id\"");
        DB::statement("ALTER TABLE \"users\" DROP COLUMN IF EXISTS \"is_superadmin\"");
    }
};
