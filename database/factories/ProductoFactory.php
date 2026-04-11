<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'empresa_id'    => 1,
            'codigo'        => $this->faker->unique()->bothify('PROD-####'),
            'nombre'        => $this->faker->words(3, true),
            'precio_compra' => $this->faker->numberBetween(10_000, 500_000),
            'precio_venta'  => $this->faker->numberBetween(15_000, 700_000),
            'iva_pct'       => 19,
            'stock_actual'  => $this->faker->numberBetween(5, 100),
            'stock_minimo'  => 2,
            'activo'        => true,
            'es_servicio'   => false,
        ];
    }

    public function servicio(): static
    {
        return $this->state([
            'es_servicio'  => true,
            'stock_actual' => 0,
            'stock_minimo' => 0,
        ]);
    }

    public function sinStock(): static
    {
        return $this->state(['stock_actual' => 0]);
    }
}
