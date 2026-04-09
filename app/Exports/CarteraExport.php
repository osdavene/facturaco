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

class CarteraExport implements
    FromCollection, WithHeadings, WithMapping,
    WithStyles, WithTitle, ShouldAutoSize
{
    public function collection()
    {
        return Factura::whereIn('estado', ['emitida', 'vencida'])
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    public function headings(): array
    {
        return [
            'N° Factura', 'Cliente', 'Documento',
            'Fecha Emisión', 'Fecha Vencimiento', 'Días Vencida',
            'Total', 'Pagado', 'Saldo Pendiente', 'Estado',
        ];
    }

    public function map($factura): array
    {
        $diasVencida = $factura->fecha_vencimiento < now()
            ? now()->diffInDays($factura->fecha_vencimiento)
            : 0;

        return [
            $factura->numero,
            $factura->cliente_nombre,
            $factura->cliente_documento,
            $factura->fecha_emision->format('d/m/Y'),
            $factura->fecha_vencimiento->format('d/m/Y'),
            $diasVencida > 0 ? $diasVencida : '—',
            $factura->total,
            $factura->total_pagado,
            max(0, $factura->total - $factura->total_pagado),
            ucfirst($factura->estado),
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

    public function title(): string { return 'Cartera'; }
}