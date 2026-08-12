<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class GenericExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting, WithStyles
{
    protected $collection;
    protected $headings;
    protected $mapping;
    protected $columnFormats;

    public function __construct($collection, array $headings, $mapping = null, array $columnFormats = [])
    {
        $this->collection = $collection;
        $this->headings = $headings;
        $this->mapping = $mapping;
        $this->columnFormats = $columnFormats;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function columnFormats(): array
    {
        return $this->columnFormats;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la primera fila (cabeceras): Negrita y fondo color melón/durazno claro
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => 'FCE4D6', // Color melón/durazno claro de la solicitud
                    ],
                ],
            ],
        ];
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

        // Si no hay mapping, devolvemos todo el array/modelo
        return is_array($row) ? $row : $row->toArray();
    }
}

