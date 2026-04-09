<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\UnidadMedida;

class InventarioSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías base
        $categorias = [
            'Papelería y Útiles',
            'Equipos de Cómputo',
            'Muebles y Enseres',
            'Aseo y Limpieza',
            'Servicios',
            'Otros',
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(['nombre' => $cat]);
        }

        // Unidades de medida base
        $unidades = [
            ['nombre' => 'Unidad',    'abreviatura' => 'UN'],
            ['nombre' => 'Kilogramo', 'abreviatura' => 'KG'],
            ['nombre' => 'Gramo',     'abreviatura' => 'GR'],
            ['nombre' => 'Litro',     'abreviatura' => 'LT'],
            ['nombre' => 'Metro',     'abreviatura' => 'MT'],
            ['nombre' => 'Caja',      'abreviatura' => 'CJ'],
            ['nombre' => 'Paquete',   'abreviatura' => 'PQ'],
            ['nombre' => 'Resma',     'abreviatura' => 'RS'],
            ['nombre' => 'Par',       'abreviatura' => 'PR'],
            ['nombre' => 'Servicio',  'abreviatura' => 'SV'],
        ];

        foreach ($unidades as $u) {
            UnidadMedida::firstOrCreate(
                ['abreviatura' => $u['abreviatura']],
                ['nombre' => $u['nombre']]
            );
        }

        $this->command->info('✅ Categorías y unidades de medida creadas.');
    }
}