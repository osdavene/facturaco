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
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class FacturasExport implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithTitle, ShouldAutoSize
{
    protected $fechaDesde;
    protected $fechaHasta;
    protected $estado;

    public function __construct($fechaDesde, $fechaHasta, $estado = null)
    {
        $this->fechaDesde = $fechaDesde;
        $this->fechaHasta = $fechaHasta;
        $this->estado     = $estado;
    }

    public function collection()
    {
        return Factura::whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta])
            ->when($this->estado, fn($q) => $q->where('estado', $this->estado))
            ->where('estado', '!=', 'anulada')
            ->orderBy('fecha_emision')
            ->get();
    }

    public function headings(): array
    {
        return [
            'N° Factura', 'Tipo', 'Fecha Emisión', 'Fecha Vencimiento',
            'Cliente', 'Documento', 'Forma Pago', 'Estado',
            'Subtotal', 'IVA', 'ReteFuente', 'ReteICA', 'Total',
            'Total Pagado', 'Saldo Pendiente',
        ];
    }

    public function map($factura): array
    {
        return [
            $factura->numero,
            ucfirst($factura->tipo),
            $factura->fecha_emision->format('d/m/Y'),
            $factura->fecha_vencimiento->format('d/m/Y'),
            $factura->cliente_nombre,
            $factura->cliente_documento,
            ucfirst($factura->forma_pago),
            ucfirst($factura->estado),
            $factura->subtotal,
            $factura->iva,
            $factura->retefuente,
            $factura->reteica,
            $factura->total,
            $factura->total_pagado,
            max(0, $factura->total - $factura->total_pagado),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID,
                           'startColor' => ['rgb' => 'F59E0B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Facturas';
    }
}