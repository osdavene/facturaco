<?php

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\Empresa;
use App\Models\Factura;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacturaFactory extends Factory
{
    protected $model = Factura::class;

    public function definition(): array
    {
        static $consecutivo = 0;
        $consecutivo++;

        $prefijo = 'FE';
        $numero  = $prefijo.'-'.date('Y').'-'.str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

        $subtotal = $this->faker->numberBetween(100_000, 5_000_000);
        $iva      = (int) ($subtotal * 0.19);

        return [
            'empresa_id'        => Empresa::factory(),
            'cliente_id'        => Cliente::factory(),
            'user_id'           => User::factory(),
            'numero'            => $numero,
            'prefijo'           => $prefijo,
            'consecutivo'       => $consecutivo,
            'tipo'              => 'factura',
            'cliente_nombre'    => $this->faker->name(),
            'cliente_documento' => 'CC: '.$this->faker->numerify('##########'),
            'fecha_emision'     => today(),
            'fecha_vencimiento' => today()->addDays(30),
            'subtotal'          => $subtotal,
            'descuento'         => 0,
            'base_iva'          => $subtotal,
            'iva'               => $iva,
            'retefuente'        => 0,
            'reteiva'           => 0,
            'reteica'           => 0,
            'total'             => $subtotal + $iva,
            'total_pagado'      => 0,
            'estado'            => 'emitida',
            'forma_pago'        => 'contado',
            'plazo_pago'        => 0,
        ];
    }
}
