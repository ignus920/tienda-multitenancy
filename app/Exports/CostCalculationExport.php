<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CostCalculationExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithColumnWidths
{
    protected $lines;
    protected $name;
    protected $total;

    public function __construct($lines, $name, $total)
    {
        $this->lines = $lines;
        $this->name = $name;
        $this->total = $total;
    }

    public function collection()
    {
        $collection = collect($this->lines);

        $collection->push((object) [
            'is_total' => true,
            'line_cost' => $this->total,
        ]);

        return $collection;
    }

    public function headings(): array
    {
        return [
            [$this->name],
            ['Origen', 'Descripción', 'Cantidad', 'Precio Unitario', 'Cant en Cm', 'Precio cm', 'Subtotal'],
        ];
    }

    public function map($row): array
    {
        if (!empty($row->is_total)) {
            return ['', '', '', '', '', 'Total', $row->line_cost];
        }

        $isCm = $row->mode === 'cm';

        return [
            $row->origin === 'erp' ? 'ERP' : 'EXT',
            $row->description,
            $isCm ? '-' : $row->qty_display,
            $isCm ? '' : $row->unit_display,
            $isCm ? $row->qty_display : '',
            $isCm ? $row->unit_display : '',
            $row->subtotal,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 55,
            'C' => 12,
            'D' => 16,
            'E' => 12,
            'F' => 12,
            'G' => 16,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                $sheet->mergeCells('A1:G1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '0000FF'], 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);

                $sheet->getStyle('A2:G2')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
                ]);

                $currencyFormat = '"$"#,##0';
                $sheet->getStyle('D3:D' . $highestRow)->getNumberFormat()->setFormatCode($currencyFormat);
                $sheet->getStyle('F3:G' . $highestRow)->getNumberFormat()->setFormatCode($currencyFormat);

                $sheet->getStyle('C3:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('F' . $highestRow . ':G' . $highestRow)->applyFromArray([
                    'font' => ['bold' => true],
                ]);
            },
        ];
    }
}
