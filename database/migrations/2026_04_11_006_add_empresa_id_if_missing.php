<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega empresa_id a todas las tablas que aún no lo tengan.
     * Usa ADD COLUMN IF NOT EXISTS (PostgreSQL ≥ 9.6) para ser idempotente.
     */
    private array $tablas = [
        'clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida',
        'facturas', 'cotizaciones', 'remisiones', 'ordenes_compra',
        'recibos_caja', 'notas_credito', 'movimientos_inventario',
    ];

    public function up(): void
    {
        $empresaId = DB::table('empresa')->value('id') ?? 1;

        foreach ($this->tablas as $tabla) {
            if (!Schema::hasTable($tabla)) {
                continue;
            }

            // ADD COLUMN IF NOT EXISTS es idempotente: no falla si ya existe
            DB::statement("
                ALTER TABLE \"{$tabla}\"
                ADD COLUMN IF NOT EXISTS \"empresa_id\" BIGINT NOT NULL DEFAULT {$empresaId}
                REFERENCES \"empresa\"(\"id\") ON DELETE CASCADE
            ");

            // Quitar el DEFAULT una vez que la columna ya existe con valor
            // (solo si la columna acaba de ser creada no tendrá datos, así que el DEFAULT es seguro)
            DB::statement("ALTER TABLE \"{$tabla}\" ALTER COLUMN \"empresa_id\" DROP DEFAULT");
        }

        // Índices únicos compuestos con empresa_id
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
            if (!Schema::hasTable($tabla)) {
                continue;
            }
            DB::statement("DROP INDEX IF EXISTS \"{$tabla}_{$col}_unique\"");
            DB::statement("
                CREATE UNIQUE INDEX IF NOT EXISTS \"{$tabla}_empresa_{$col}_unique\"
                ON \"{$tabla}\" (\"empresa_id\", \"{$col}\")
            ");
        }

        if (Schema::hasTable('productos')) {
            DB::statement("DROP INDEX IF EXISTS \"productos_codigo_barras_unique\"");
            DB::statement("
                CREATE UNIQUE INDEX IF NOT EXISTS \"productos_empresa_codigo_barras_unique\"
                ON \"productos\" (\"empresa_id\", \"codigo_barras\")
                WHERE \"codigo_barras\" IS NOT NULL
            ");
        }
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
