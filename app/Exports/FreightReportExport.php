<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FreightReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $collection;
    protected $headings;
    protected $mapping;

    public function __construct($collection, array $headings, $mapping = null)
    {
        $this->collection = $collection;
        $this->headings = $headings;
        $this->mapping = $mapping;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        if (is_callable($this->mapping)) {
            return call_user_func($this->mapping, $row);
        }

        if (is_array($this->mapping)) {
            $mappedRow = [];
            foreach ($this->mapping as $column) {
                $mappedRow[] = data_get($row, $column);
            }
            return $mappedRow;
        }

        return is_array($row) ? $row : $row->toArray();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la primera fila (Cabecera)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFFCE4D6'] // Color salmón/durazno claro (FCE4D6)
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
        ];
    }
}
