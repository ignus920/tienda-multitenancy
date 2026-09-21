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

class ChatbotRequestsExport implements FromCollection, WithHeadings, WithMapping, WithEvents, WithColumnWidths
{
    protected $requests;

    public function __construct($requests)
    {
        $this->requests = $requests;
    }

    public function collection()
    {
        return $this->requests;
    }

    public function headings(): array
    {
        return [
            ['Bandeja de Solicitudes de Garantía Web (Chatbot)'],
            [
                'Fecha',
                'Radicado',
                'Factura / REF',
                'Empresa (Cliente)',
                'Productos y Fallas',
                'Estado'
            ]
        ];
    }

    public function map($request): array
    {
        $status = 'Pendiente';
        if ($request->status === 'processed') $status = 'Procesada';
        if ($request->status === 'rejected') $status = 'Rechazada';

        return [
            $request->created_at->format('d/m/Y H:i'),
            $request->tracking_code ?? 'N/A',
            $request->reference_number ?? 'N/A',
            $request->company_name ?? 'N/A',
            $request->product_details ?? 'N/A',
            $status,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Fecha
            'B' => 20, // Radicado
            'C' => 25, // Factura
            'D' => 45, // Empresa
            'E' => 60, // Productos y Fallas (Maximo acotado, segun reglas)
            'F' => 15, // Estado
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Merge title row
                $event->sheet->getDelegate()->mergeCells('A1:F1');

                // Título principal
                $event->sheet->getDelegate()->getStyle('A1:F1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['argb' => 'FFFFFF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => '4F46E5', // Indigo-600
                        ],
                    ],
                ]);
                $event->sheet->getDelegate()->getRowDimension(1)->setRowHeight(30);

                // Estilo para los encabezados (Fila 2) según regla: negrita, centrada, color salmón #FCE4D6
                $event->sheet->getDelegate()->getStyle('A2:F2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FCE4D6',
                        ],
                    ],
                ]);

                // Ajuste de texto (WrapText) para la columna de descripciones largas (E)
                $highestRow = $event->sheet->getDelegate()->getHighestRow();
                $event->sheet->getDelegate()->getStyle('E3:E' . $highestRow)
                    ->getAlignment()->setWrapText(true);
                
                // Alineación superior para todas las celdas
                $event->sheet->getDelegate()->getStyle('A3:F' . $highestRow)
                    ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            },
        ];
    }
}
