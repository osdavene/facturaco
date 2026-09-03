<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanesSeeder extends Seeder
{
    public function run(): void
    {
        $planes = [
            [
                'nombre'               => 'Plan Emprendedor',
                'slug'                 => 'plan-emprendedor',
                'descripcion'          => 'Ideal para microempresas, profesionales independientes y consultores.',
                'precio'               => 39000.00,
                'limite_facturas_mes'  => 100,
                'limite_usuarios'      => 2,
                'limite_productos'     => 100,
                'soporta_dian'         => true,
                'soporta_pos'          => true,
                'soporta_nomina'       => false,
                'soporta_contabilidad' => false,
                'color'                => 'blue',
                'destacado'            => false,
                'activo'               => true,
                'orden'                => 1,
            ],
            [
                'nombre'               => 'Plan Comercio / Pyme',
                'slug'                 => 'plan-comercio',
                'descripcion'          => 'Para tiendas, distribuidoras y negocios con punto de venta e inventario activo.',
                'precio'               => 69000.00,
                'limite_facturas_mes'  => 300,
                'limite_usuarios'      => 5,
                'limite_productos'     => 1000,
                'soporta_dian'         => true,
                'soporta_pos'          => true,
                'soporta_nomina'       => true,
                'soporta_contabilidad' => false,
                'color'                => 'amber',
                'destacado'            => true,
                'activo'               => true,
                'orden'                => 2,
            ],
            [
                'nombre'               => 'Plan Corporativo Pro',
                'slug'                 => 'plan-corporativo-pro',
                'descripcion'          => 'Solución completa con nómina electrónica, compras, cotizaciones y contabilidad.',
                'precio'               => 119000.00,
                'limite_facturas_mes'  => 1000,
                'limite_usuarios'      => 15,
                'limite_productos'     => null,
                'soporta_dian'         => true,
                'soporta_pos'          => true,
                'soporta_nomina'       => true,
                'soporta_contabilidad' => true,
                'color'                => 'emerald',
                'destacado'            => false,
                'activo'               => true,
                'orden'                => 3,
            ],
            [
                'nombre'               => 'Plan Enterprise Ilimitado',
                'slug'                 => 'plan-enterprise-ilimitado',
                'descripcion'          => 'Emisión masiva de facturas sin límites, sucursales múltiples y soporte prioritario.',
                'precio'               => 199000.00,
                'limite_facturas_mes'  => null,
                'limite_usuarios'      => null,
                'limite_productos'     => null,
                'soporta_dian'         => true,
                'soporta_pos'          => true,
                'soporta_nomina'       => true,
                'soporta_contabilidad' => true,
                'color'                => 'purple',
                'destacado'            => false,
                'activo'               => true,
                'orden'                => 4,
            ],
        ];

        foreach ($planes as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // Asignar Plan Comercio a la empresa actual si no tiene plan
        $planComercio = Plan::where('slug', 'plan-comercio')->first();
        if ($planComercio) {
            Empresa::whereNull('plan_id')->update([
                'plan_id'          => $planComercio->id,
                'plan_vencimiento' => now()->addYear(),
            ]);
        }
    }
}
