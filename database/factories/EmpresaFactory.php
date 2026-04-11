<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'razon_social'    => $this->faker->company(),
            'nit'             => $this->faker->unique()->numerify('9########'),
            'digito_verificacion' => $this->faker->numberBetween(0, 9),
            'tipo_persona'    => 'juridica',
            'regimen'         => 'responsable_iva',
            'email'           => $this->faker->companyEmail(),
            'telefono'        => $this->faker->phoneNumber(),
            'prefijo_factura' => 'FE',
            'moneda'          => 'COP',
            'decimales'       => 0,
            'iva_defecto'     => 19,
        ];
    }
}
