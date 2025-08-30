<?php

namespace App\Exports;

use App\Models\Receiving;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReceivingsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($receiving): array
    {
        return [
            $receiving->receiving_number,
            $receiving->item->name,
            $receiving->item->code,
            $receiving->quantity,
            $receiving->unit->name,
            number_format($receiving->unit_price, 2),
            number_format($receiving->quantity * $receiving->unit_price, 2),
            $receiving->supplier->name,
            $receiving->department->name,
            \Carbon\Carbon::parse($receiving->received_at)->format('Y-m-d'),
        ];
    }

    public function headings(): array
    {
        return [
            __('messages.receiving_number'),
            __('messages.item'),
            __('messages.item_code'),
            __('messages.quantity'),
            __('messages.unit'),
            __('messages.unit_price'),
            __('messages.total'),
            __('messages.supplier'),
            __('messages.department'),
            __('messages.date'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Get the last row number
        $lastRow = $sheet->getHighestRow();
        
        // Style for headers
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4A90E2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style for data cells
        $sheet->getStyle('A2:J'.$lastRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Alternate row colors
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A'.$row.':J'.$row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
            }
        }

        // Set row height
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Auto-fit columns
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Receiving Number
            'B' => 30, // Item
            'C' => 15, // Item Code
            'D' => 10, // Quantity
            'E' => 10, // Unit
            'F' => 15, // Unit Price
            'G' => 15, // Total
            'H' => 25, // Supplier
            'I' => 25, // Department
            'J' => 15, // Date
        ];
    }
}