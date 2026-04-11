<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Repara el caso en que _002 quedó registrada pero los ALTER TABLE
     * no se aplicaron. En SQLite (tests) siempre es un no-op porque _002 ya corrió.
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
            if (Schema::hasTable($tabla) && !Schema::hasColumn($tabla, 'empresa_id')) {
                if ($driver === 'sqlite') {
                    Schema::table($tabla, function (Blueprint $table) use ($empresaId) {
                        $table->unsignedBigInteger('empresa_id')->default($empresaId)->after('id');
                    });
                } else {
                    DB::statement("
                        ALTER TABLE \"{$tabla}\"
                        ADD COLUMN \"empresa_id\" BIGINT NOT NULL DEFAULT {$empresaId}
                        REFERENCES \"empresa\"(\"id\") ON DELETE CASCADE
                    ");
                    DB::statement("ALTER TABLE \"{$tabla}\" ALTER COLUMN \"empresa_id\" DROP DEFAULT");
                }
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
            if (!Schema::hasTable($tabla) || !Schema::hasColumn($tabla, 'empresa_id')) {
                continue;
            }
            $constraintName = "{$tabla}_{$col}_unique";
            $newIndex       = "{$tabla}_empresa_{$col}_unique";

            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE \"{$tabla}\" DROP CONSTRAINT IF EXISTS \"{$constraintName}\"");
                DB::statement("DROP INDEX IF EXISTS \"{$constraintName}\"");
            } else {
                DB::statement("DROP INDEX IF EXISTS \"{$constraintName}\"");
            }

            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"{$newIndex}\" ON \"{$tabla}\" (\"empresa_id\", \"{$col}\")");
        }

        if (Schema::hasTable('productos') && Schema::hasColumn('productos', 'empresa_id')) {
            $constraintName = 'productos_codigo_barras_unique';
            if ($driver === 'pgsql') {
                DB::statement("ALTER TABLE \"productos\" DROP CONSTRAINT IF EXISTS \"{$constraintName}\"");
                DB::statement("DROP INDEX IF EXISTS \"{$constraintName}\"");
            } else {
                DB::statement("DROP INDEX IF EXISTS \"{$constraintName}\"");
            }

            if ($driver === 'sqlite') {
                DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"productos_empresa_codigo_barras_unique\" ON \"productos\" (\"empresa_id\", \"codigo_barras\")");
            } else {
                DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"productos_empresa_codigo_barras_unique\" ON \"productos\" (\"empresa_id\", \"codigo_barras\") WHERE \"codigo_barras\" IS NOT NULL");
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
