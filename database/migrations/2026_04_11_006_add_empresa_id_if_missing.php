<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Idempotente: agrega empresa_id si falta y recrea índices únicos compuestos.
     * Compatible con PostgreSQL (producción) y SQLite (tests).
     */
    private array $tablas = [
        'clientes', 'proveedores', 'productos', 'categorias', 'unidades_medida',
        'facturas', 'cotizaciones', 'remisiones', 'ordenes_compra',
        'recibos_caja', 'notas_credito', 'movimientos_inventario',
    ];

    public function up(): void
    {
        $empresaId = DB::table('empresa')->value('id') ?? 1;
        $driver    = DB::getDriverName();

        foreach ($this->tablas as $tabla) {
            if (!Schema::hasTable($tabla) || Schema::hasColumn($tabla, 'empresa_id')) {
                continue;
            }

            if ($driver === 'sqlite') {
                Schema::table($tabla, function (Blueprint $table) use ($empresaId) {
                    $table->unsignedBigInteger('empresa_id')->default($empresaId)->after('id');
                });
            } else {
                DB::statement("
                    ALTER TABLE \"{$tabla}\"
                    ADD COLUMN IF NOT EXISTS \"empresa_id\" BIGINT NOT NULL DEFAULT {$empresaId}
                    REFERENCES \"empresa\"(\"id\") ON DELETE CASCADE
                ");
                DB::statement("ALTER TABLE \"{$tabla}\" ALTER COLUMN \"empresa_id\" DROP DEFAULT");
            }
        }

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
            $oldIndex = "{$tabla}_{$col}_unique";
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE \"{$tabla}\" DROP CONSTRAINT IF EXISTS \"{$oldIndex}\"");
                DB::statement("DROP INDEX IF EXISTS \"{$oldIndex}\"");
            } else {
                DB::statement("DROP INDEX IF EXISTS \"{$oldIndex}\"");
            }
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"{$tabla}_empresa_{$col}_unique\" ON \"{$tabla}\" (\"empresa_id\", \"{$col}\")");
        }

        if (Schema::hasTable('productos')) {
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE \"productos\" DROP CONSTRAINT IF EXISTS \"productos_codigo_barras_unique\"");
                DB::statement("DROP INDEX IF EXISTS \"productos_codigo_barras_unique\"");
                DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"productos_empresa_codigo_barras_unique\" ON \"productos\" (\"empresa_id\", \"codigo_barras\") WHERE \"codigo_barras\" IS NOT NULL");
            } else {
                DB::statement("DROP INDEX IF EXISTS \"productos_codigo_barras_unique\"");
                DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"productos_empresa_codigo_barras_unique\" ON \"productos\" (\"empresa_id\", \"codigo_barras\")");
            }
        }
    }

    public function down(): void
    {
        foreach ($this->tablas as $tabla) {
            try {
                if (DB::getDriverName() === 'sqlite') {
                    Schema::table($tabla, fn (Blueprint $t) => $t->dropColumn('empresa_id'));
                } else {
                    DB::statement("ALTER TABLE \"{$tabla}\" DROP COLUMN IF EXISTS \"empresa_id\"");
                }
            } catch (\Throwable) {}
        }
    }
};
