<?php

namespace Database\Seeders;

use App\Models\Empresa;
use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        \$empresa = Empresa::obtener();
        
        Producto::factory()
            ->count(10)
            ->create([
                'empresa_id' => \$empresa->id,
                'activo' => true,
            ]);
    }
}

