<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectMaterialsExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithColumnWidths
{
    protected $materials;
    protected $projectName;
    protected $clientName;
    protected $total;

    public function __construct($materials, $projectName, $clientName)
    {
        $this->materials = $materials;
        $this->projectName = $projectName;
        $this->clientName = $clientName;
        $this->total = $materials->where('is_active', true)->sum('line_cost');
    }

    public function collection()
    {
        $collection = collect($this->materials);
        
        $collection->push((object)[
            'origin' => '',
            'description' => '',
            'quantity' => 'Total',
            'unit_value' => '',
            'line_cost' => $this->total,
            'observations' => '',
            'is_total' => true
        ]);

        return $collection;
    }

    public function headings(): array
    {
        return [
            [$this->projectName . ' - ' . $this->clientName],
            ['Origen', 'Descripción', 'Cantidad', 'Precio Unitario', 'Subtotal', 'Observaciones']
        ];
    }

    public function map($row): array
    {
        if (isset($row->is_total) && $row->is_total) {
            return [
                '',
                '',
                '',
                'Total',
                $row->line_cost,
                ''
            ];
        }

        return [
            $row->origin === 'erp' ? 'ERP' : 'Externo',
            $row->description . (!$row->is_active ? ' (Desactivado: ' . $row->deactivation_reason . ')' : ''),
            $row->quantity,
            $row->unit_value,
            $row->is_active ? $row->line_cost : 0,
            $row->observations,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // Origen
            'B' => 50,  // Desc (reducida un poco, antes AutoSize o más ancha)
            'C' => 10,  // Cant
            'D' => 15,  // Precio
            'E' => 15,  // Subtotal
            'F' => 60,  // Obs (ampliada)
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Fila 1: Título
                $sheet->mergeCells('A1:F1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '0000FF'],
                        'size' => 12
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                // Fila 2: Cabeceras
                $sheet->getStyle('A2:F2')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FCE4D6']
                    ]
                ]);

                // Moneda sin decimales
                $currencyFormat = '"$"#,##0';
                $sheet->getStyle('D3:E' . $highestRow)->getNumberFormat()->setFormatCode($currencyFormat);

                // Cantidades centradas
                $sheet->getStyle('C3:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Total Fila bold
                $sheet->getStyle('C' . $highestRow . ':E' . $highestRow)->applyFromArray([
                    'font' => ['bold' => true]
                ]);
            }
        ];
    }
}
