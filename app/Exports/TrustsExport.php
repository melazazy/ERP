<?php

namespace App\Exports;

use App\Models\Trust;
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

class TrustsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
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

    public function map($trust): array
    {
        return [
            $trust->requisition_number,
            $trust->item->name,
            $trust->item->code,
            $trust->quantity,
            $trust->department->name,
            $trust->user->name,
            __('messages.' . $trust->status),
            \Carbon\Carbon::parse($trust->requested_date)->format('Y-m-d'),
        ];
    }

    public function headings(): array
    {
        return [
            __('messages.requisition_number'),
            __('messages.item'),
            __('messages.item_code'),
            __('messages.quantity'),
            __('messages.department'),
            __('messages.requested_by'),
            __('messages.status'),
            __('messages.requested_date'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Get the last row number
        $lastRow = $sheet->getHighestRow();
        
        // Style for headers
        $sheet->getStyle('A1:H1')->applyFromArray([
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
        $sheet->getStyle('A2:H'.$lastRow)->applyFromArray([
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

        // Status column styling
        $sheet->getStyle('G2:G'.$lastRow)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Item code styling
        $sheet->getStyle('C2:C'.$lastRow)->applyFromArray([
            'font' => [
                'name' => 'Consolas',
            ],
        ]);

        // Alternate row colors
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A'.$row.':H'.$row)->applyFromArray([
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
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Requisition Number
            'B' => 30, // Item Name
            'C' => 15, // Item Code
            'D' => 10, // Quantity
            'E' => 25, // Department
            'F' => 25, // Requested By
            'G' => 15, // Status
            'H' => 15, // Requested Date
        ];
    }
}
