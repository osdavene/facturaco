<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'empresa_id'       => 1,
            'tipo_persona'     => 'natural',
            'tipo_documento'   => 'CC',
            'numero_documento' => $this->faker->unique()->numerify('1#########'),
            'nombres'          => $this->faker->firstName(),
            'apellidos'        => $this->faker->lastName(),
            'email'            => $this->faker->safeEmail(),
            'celular'          => $this->faker->numerify('3#########'),
            'municipio'        => 'Bogotá',
            'departamento'     => 'Cundinamarca',
            'pais'             => 'Colombia',
            'retefuente_pct'   => 0,
            'reteiva_pct'      => 0,
            'reteica_pct'      => 0,
            'plazo_pago'       => 0,
            'activo'           => true,
        ];
    }

    public function juridica(): static
    {
        return $this->state([
            'tipo_persona'   => 'juridica',
            'tipo_documento' => 'NIT',
            'razon_social'   => $this->faker->company(),
            'nombres'        => null,
            'apellidos'      => null,
        ]);
    }

    public function conRetenciones(): static
    {
        return $this->state([
            'retefuente_pct' => 3.5,
            'reteiva_pct'    => 15,
            'reteica_pct'    => 0.414,
        ]);
    }
}
