<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $driver    = DB::getDriverName();

        foreach ($this->tablas as $tabla) {
            if (!Schema::hasColumn($tabla, 'empresa_id')) {
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

        $this->reemplazarUniques($driver);
    }

    private function reemplazarUniques(string $driver): void
    {
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
            $this->reemplazarUnique($tabla, $col, $driver);
        }

        $this->reemplazarUnique('productos', 'codigo_barras', $driver, nullable: true);
    }

    private function reemplazarUnique(string $tabla, string $columna, string $driver, bool $nullable = false): void
    {
        $oldIndex = "{$tabla}_{$columna}_unique";
        $newIndex = "{$tabla}_empresa_{$columna}_unique";

        if ($driver === 'pgsql') {
            // Blueprint->unique() crea una CONSTRAINT en PostgreSQL, no solo un índice.
            // Hay que usar DROP CONSTRAINT (con IF EXISTS para que no aborte la transacción).
            DB::statement("ALTER TABLE \"{$tabla}\" DROP CONSTRAINT IF EXISTS \"{$oldIndex}\"");
            // Por si en algún entorno existía como índice suelto:
            DB::statement("DROP INDEX IF EXISTS \"{$oldIndex}\"");
        } else {
            DB::statement("DROP INDEX IF EXISTS \"{$oldIndex}\"");
        }

        if ($nullable && $driver === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"{$newIndex}\" ON \"{$tabla}\" (\"empresa_id\", \"{$columna}\") WHERE \"{$columna}\" IS NOT NULL");
        } else {
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS \"{$newIndex}\" ON \"{$tabla}\" (\"empresa_id\", \"{$columna}\")");
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
