<?php
namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InventarioExport implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        return Producto::with(['categoria', 'unidadMedida'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Código', 'Nombre', 'Categoría', 'Unidad',
            'Tipo', 'Stock Actual', 'Stock Mínimo', 'Stock Máximo',
            'Precio Compra', 'Precio Venta', '% IVA',
            'Valor en Stock', 'Estado Stock',
        ];
    }

    public function map($producto): array
    {
        $estadoStock = $producto->es_servicio ? 'Servicio'
                     : ($producto->stock_actual == 0 ? 'Sin stock'
                     : ($producto->bajo_stock      ? 'Bajo stock' : 'OK'));

        return [
            $producto->codigo,
            $producto->nombre,
            $producto->categoria->nombre ?? '—',
            $producto->unidadMedida->simbolo ?? 'UN',
            $producto->es_servicio ? 'Servicio' : 'Producto',
            $producto->es_servicio ? '—' : $producto->stock_actual,
            $producto->es_servicio ? '—' : $producto->stock_minimo,
            $producto->es_servicio ? '—' : $producto->stock_maximo,
            $producto->precio_compra,
            $producto->precio_venta,
            $producto->iva_pct . '%',
            $producto->es_servicio ? 0 : ($producto->stock_actual * $producto->precio_compra),
            $estadoStock,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID,
                           'startColor' => ['rgb' => 'F59E0B']],
            ],
        ];
    }

    public function title(): string { return 'Inventario'; }
}