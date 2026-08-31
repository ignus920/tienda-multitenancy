<?php

namespace App\Exports\Tenant;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ImportsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting, WithColumnWidths
{
    protected $items;

    public function __construct($items)
    {
        $this->items = $items;
    }

    public function collection()
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Código SKU',
            'Descripción',
            'Existencias ERP',
            'Cantidad Solicitada',
            'Porcentaje Rotación',
            'Salidas ERP',
            'Entradas ERP',
            'EXW',
            'Prioridad'
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->sku,
            $item->description ?? $item->name,
            $item->stock_items_store,
            $item->quantity,
            ($item->percentage ?? 0) / 100,
            $item->outsideMovement,
            $item->insideMovement,
            $item->exw,
            $item->priority ?? 'Sin asignar'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Ajustar texto en la columna de descripción
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFCE4D6']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'C' => 60, // Límite para la descripción
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_PERCENTAGE,
            'I' => '"$"#,##0.00',
        ];
    }
}
