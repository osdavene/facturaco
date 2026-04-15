<?php

namespace Database\Seeders;

use App\Models\Modulo;
use Illuminate\Database\Seeder;

class ModuloSeeder extends Seeder
{
    /**
     * Seed the application's module catalog.
     */
    public function run(): void
    {
        $modulos = [
            [
                'nombre' => 'Facturación',
                'slug' => 'facturacion',
                'descripcion' => 'Facturas, notas crédito, impuestos y reportes de ventas/cartera.',
                'activo' => true,
            ],
            [
                'nombre' => 'Inventario',
                'slug' => 'inventario',
                'descripcion' => 'Productos, categorías, unidades y reportes de inventario.',
                'activo' => true,
            ],
            [
                'nombre' => 'Contable',
                'slug' => 'contable',
                'descripcion' => 'Recibos de caja y operaciones contables.',
                'activo' => true,
            ],
            [
                'nombre' => 'Nómina',
                'slug' => 'nomina',
                'descripcion' => 'Gestión de nómina.',
                'activo' => true,
            ],
        ];

        foreach ($modulos as $modulo) {
            Modulo::updateOrCreate(
                ['slug' => $modulo['slug']],
                $modulo
            );
        }
    }
}
