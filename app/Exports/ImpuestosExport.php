<?php
namespace App\Exports;

use App\Models\Factura;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ImpuestosExport implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithTitle, ShouldAutoSize
{
    protected $fechaDesde;
    protected $fechaHasta;

    public function __construct($fechaDesde, $fechaHasta)
    {
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
    }

    public function collection()
    {
        return Factura::whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta])
            ->whereNotIn('estado', ['anulada', 'borrador'])
            ->orderBy('fecha_emision')
            ->get();
    }

    public function headings(): array
    {
        return [
            'N° Factura', 'Fecha', 'Cliente', 'Estado',
            'Subtotal/Base', 'IVA 19%', 'IVA 5%', 'IVA 0%',
            'ReteFuente', 'ReteICA', 'Total',
        ];
    }

    public function map($f): array
    {
        return [
            $f->numero,
            $f->fecha_emision->format('d/m/Y'),
            $f->cliente_nombre,
            ucfirst($f->estado),
            $f->subtotal,
            $f->iva,
            0,
            0,
            $f->retefuente,
            $f->reteica,
            $f->total,
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

    public function title(): string { return 'Impuestos DIAN'; }
}