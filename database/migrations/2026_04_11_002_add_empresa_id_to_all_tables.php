<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $tablas = [
        'clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida',
        'facturas', 'cotizaciones', 'remisiones', 'ordenes_compra',
        'recibos_caja', 'notas_credito', 'movimientos_inventario',
    ];

    public function up(): void
    {
        $empresaId = DB::table('empresa')->value('id') ?? 1;

        foreach ($this->tablas as $tabla) {
            if (!Schema::hasColumn($tabla, 'empresa_id')) {
                // SQL puro para PostgreSQL:
                // 1. Agregar columna NOT NULL con DEFAULT temporal (necesario para filas existentes)
                DB::statement("
                    ALTER TABLE \"{$tabla}\"
                    ADD COLUMN \"empresa_id\" BIGINT NOT NULL DEFAULT {$empresaId}
                    REFERENCES \"empresa\"(\"id\") ON DELETE CASCADE
                ");
                // 2. Quitar el DEFAULT (la columna queda NOT NULL sin default)
                DB::statement("ALTER TABLE \"{$tabla}\" ALTER COLUMN \"empresa_id\" DROP DEFAULT");
            }
        }

        // ── Reemplazar unique constraints simples por compuestos (empresa_id, columna) ──

        $uniques = [
            'facturas'       => 'numero',
            'cotizaciones'   => 'numero',
            'remisiones'     => 'numero',
            'ordenes_compra' => 'numero',
            'recibos_caja'   => 'numero',
            'notas_credito'  => 'numero',
            'productos'      => 'codigo',
        ];

        foreach ($uniques as $tabla => $col) {
            $this->reemplazarUnique($tabla, $col);
        }

        // codigo_barras nullable aparte
        $this->reemplazarUnique('productos', 'codigo_barras');
    }

    private function reemplazarUnique(string $tabla, string $columna): void
    {
        $oldIndex = "{$tabla}_{$columna}_unique";
        $newIndex = "{$tabla}_empresa_{$columna}_unique";

        // Eliminar el índice único simple si existe
        DB::statement("DROP INDEX IF EXISTS \"{$oldIndex}\"");

        // Crear el índice único compuesto si no existe aún
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS \"{$newIndex}\"
            ON \"{$tabla}\" (\"empresa_id\", \"{$columna}\")
        ");
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            try {
                DB::statement("ALTER TABLE \"{$tabla}\" DROP COLUMN IF EXISTS \"empresa_id\"");
            } catch (\Throwable) {}
        }
    }
};
