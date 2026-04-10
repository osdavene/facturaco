<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Tablas que recibirán empresa_id */
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
                Schema::table($tabla, function (Blueprint $table) {
                    $table->foreignId('empresa_id')
                          ->nullable()
                          ->after('id')
                          ->constrained('empresa')
                          ->cascadeOnDelete();
                });

                // Asignar empresa_id = 1 a todos los registros existentes
                DB::table($tabla)->whereNull('empresa_id')->update(['empresa_id' => $empresaId]);

                // Ahora hacer NOT NULL
                Schema::table($tabla, function (Blueprint $table) {
                    $table->foreignId('empresa_id')->nullable(false)->change();
                });
            }
        }

        // ── Cambiar unique constraints de columna simple a (empresa_id, columna) ──

        // facturas.numero
        $this->reemplazarUnique('facturas', 'numero', 'facturas_numero_unique');
        // cotizaciones.numero
        $this->reemplazarUnique('cotizaciones', 'numero', 'cotizaciones_numero_unique');
        // remisiones.numero
        $this->reemplazarUnique('remisiones', 'numero', 'remisiones_numero_unique');
        // ordenes_compra.numero
        $this->reemplazarUnique('ordenes_compra', 'numero', 'ordenes_compra_numero_unique');
        // recibos_caja.numero
        $this->reemplazarUnique('recibos_caja', 'numero', 'recibos_caja_numero_unique');
        // notas_credito.numero
        $this->reemplazarUnique('notas_credito', 'numero', 'notas_credito_numero_unique');
        // productos.codigo
        $this->reemplazarUnique('productos', 'codigo', 'productos_codigo_unique');
        // productos.codigo_barras
        $this->reemplazarUnique('productos', 'codigo_barras', 'productos_codigo_barras_unique');
    }

    private function reemplazarUnique(string $tabla, string $columna, string $indexName): void
    {
        // Eliminar unique simple si existe
        try {
            Schema::table($tabla, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        } catch (\Throwable) {
            // El índice no existía, continuar
        }

        // Crear unique compuesto (empresa_id, columna)
        $nuevoIndex = "{$tabla}_empresa_{$columna}_unique";
        try {
            Schema::table($tabla, function (Blueprint $table) use ($columna, $nuevoIndex) {
                $table->unique(['empresa_id', $columna], $nuevoIndex);
            });
        } catch (\Throwable) {
            // El índice compuesto ya existía
        }
    }

    public function down(): void
    {
        // Restaurar uniques simples
        $columnas = [
            'facturas'       => 'numero',
            'cotizaciones'   => 'numero',
            'remisiones'     => 'numero',
            'ordenes_compra' => 'numero',
            'recibos_caja'   => 'numero',
            'notas_credito'  => 'numero',
            'productos'      => 'codigo',
        ];

        foreach ($columnas as $tabla => $col) {
            try {
                Schema::table($tabla, fn(Blueprint $t) =>
                    $t->dropUnique(["{$tabla}_empresa_{$col}_unique"])
                );
                Schema::table($tabla, fn(Blueprint $t) =>
                    $t->unique($col)
                );
            } catch (\Throwable) {}
        }

        foreach ($this->tablas as $tabla) {
            Schema::table($tabla, function (Blueprint $table) {
                $table->dropConstrainedForeignId('empresa_id');
            });
        }
    }
};
