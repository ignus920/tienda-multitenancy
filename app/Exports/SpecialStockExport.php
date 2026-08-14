<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SpecialStockExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithColumnFormatting
{
    protected $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'Código',
            'Descripción',
            'Cant. en Cuarentena',
            'Ultima obsevación',
            'Cant. en Vitrina',
            'Observación Última Vitrina / Exhibición'
        ];
    }

    public function map($item): array
    {
        $lastQuarantine = $item->quarantineMovements->sortByDesc('created_at')->first();
        $lastShowroom = $item->showroomMovements->sortByDesc('created_at')->first();

        // Convertir las cantidades a int para asegurar que no se envíen vacías y muestren el cero en Excel
        return [
            $item->internal_code ?? $item->sku,
            $item->name,
            (int) ($item->quarantine_stock ?? 0),
            $lastQuarantine ? $lastQuarantine->justification : '',
            (int) ($item->showroom_stock ?? 0),
            $lastShowroom ? $lastShowroom->justification : ''
        ];
    }

    public function columnWidths(): array
    {
        // Anchos de columna acomodados según requerimiento
        return [
            'A' => 15, // Código (Centrado)
            'B' => 45, // Descripción (Alineado izquierda)
            'C' => 20, // Cant. en Cuarentena (Centrado)
            'D' => 30, // Última observación (Alineado izquierda)
            'E' => 15, // Cant. en Vitrina (Centrado)
            'F' => 40, // Observación Última Vitrina / Exhibición (Alineado izquierda)
        ];
    }

    public function columnFormats(): array
    {
        // Formato para asegurar que se muestre el cero '0' en las columnas numéricas
        return [
            'C' => '0',
            'E' => '0',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        // Estilos para la cabecera (Fila 1)
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        // Relleno melocotón/naranja suave en la cabecera
        $sheet->getStyle('A1:F1')->getFill()->applyFromArray([
            'fillType' => Fill::FILL_SOLID,
            'startColor' => [
                'rgb' => 'FCE4D6'
            ]
        ]);

        // Centrado de columnas específicas (Código, Cantidades)
        $sheet->getStyle('A2:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E2:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Alineación a la izquierda para los campos de texto
        $sheet->getStyle('B2:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('D2:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('F2:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Ajustar alineación vertical al centro para todas las celdas
        $sheet->getStyle('A1:F' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Agregar cuadrícula con bordes delgados a toda la tabla
        $sheet->getStyle('A1:F' . $lastRow)->getBorders()->getAllBorders()->applyFromArray([
            'borderStyle' => Border::BORDER_THIN,
            'color' => [
                'rgb' => 'D9D9D9'
            ]
        ]);

        return [];
    }
}
