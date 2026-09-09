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
            'Cantidad',
            '% Stock',
            'Salidas 7 Meses',
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
            (string) ($item->stock_items_store ?? 0),
            (string) ($item->quantity ?? 0),
            (string) ($item->percentage ?? 0) . '%',
            (string) ($item->outsideMovement ?? 0),
            (string) ($item->insideMovement ?? 0),
            $item->exw,
            $item->priority ?? 'Sin asignar'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Ajustar texto en la columna de descripción
        $sheet->getStyle('C')->getAlignment()->setWrapText(true);
        
        // Centrar las columnas de cantidades y porcentajes (D hasta H)
        $sheet->getStyle('D:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        
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
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => '"$"#,##0.00',
        ];
    }
}
