<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProximamenteController extends Controller
{
    public function remisiones()
    {
        return view('proximamente', [
            'titulo'     => 'Remisiones',
            'descripcion'=> 'Genera remisiones de despacho de mercancía sin necesidad de facturar. Controla qué salió del almacén y cuándo.',
            'icono'      => 'fa-receipt',
            'color'      => 'blue',
            'features'   => [
                'Despacho de mercancía sin factura',
                'Conversión a factura con un clic',
                'Control de entregas parciales',
                'PDF profesional con firma',
                'Historial de remisiones por cliente',
            ],
        ]);
    }

    public function impuestos()
    {
        return view('proximamente', [
            'titulo'     => 'Impuestos / DIAN',
            'descripcion'=> 'Panel completo para el manejo de obligaciones tributarias. Calcula IVA, ReteFuente y ReteICA automáticamente para tus declaraciones.',
            'icono'      => 'fa-percent',
            'color'      => 'emerald',
            'features'   => [
                'Resumen de IVA por período',
                'ReteFuente y ReteICA acumulados',
                'Borrador declaración bimestral',
                'Exportar a Excel para contador',
                'Alertas de fechas límite DIAN',
            ],
        ]);
    }
}