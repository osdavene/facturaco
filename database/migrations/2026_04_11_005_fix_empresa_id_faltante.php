<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esta migración repara el caso en que la migración
     * 2026_04_11_002_add_empresa_id_to_all_tables quedó
     * registrada en la tabla migrations pero los ALTER TABLE
     * no se aplicaron realmente en la base de datos.
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
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'empresa_id')) {
                DB::statement("
                    ALTER TABLE \"{$tabla}\"
                    ADD COLUMN \"empresa_id\" BIGINT NOT NULL DEFAULT {$empresaId}
                    REFERENCES \"empresa\"(\"id\") ON DELETE CASCADE
                ");
                DB::statement("ALTER TABLE \"{$tabla}\" ALTER COLUMN \"empresa_id\" DROP DEFAULT");
            }
        }

        // Reemplazar unique constraints simples por compuestos (empresa_id, columna)
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
            if (Schema::hasTable($tabla) && Schema::hasColumn($tabla, 'empresa_id')) {
                DB::statement("DROP INDEX IF EXISTS \"{$tabla}_{$col}_unique\"");
                DB::statement("
                    CREATE UNIQUE INDEX IF NOT EXISTS \"{$tabla}_empresa_{$col}_unique\"
                    ON \"{$tabla}\" (\"empresa_id\", \"{$col}\")
                ");
            }
        }

        if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'empresa_id')) {
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
