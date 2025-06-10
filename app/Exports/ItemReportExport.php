<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $items;
    protected $selectedMonth;
    protected $selectedDepartment;
    protected $search;

    public function __construct($items, $selectedMonth, $selectedDepartment, $search)
    {
        $this->items = $items;
        $this->selectedMonth = $selectedMonth;
        $this->selectedDepartment = $selectedDepartment;
        $this->search = $search;
    }

    public function collection()
    {
        try {
            // Convert items array to collection if it's not already
            $itemsCollection = collect($this->items);
            
            // Create a new collection for the mapped data
            $data = collect();
            
            // Map the items and add them to the collection
            $itemsCollection->each(function ($item) use ($data) {
                try {
                    $data->push([
                        $item['code'] ?? '',
                        $item['name'] ?? '',
                        $item['unit'] ?? '',
                        number_format($item['opening_quantity'] ?? 0, 2),
                        number_format($item['opening_unit_cost'] ?? 0, 2),
                        number_format($item['opening_amount'] ?? 0, 2),
                        number_format($item['in_quantity'] ?? 0, 2),
                        number_format($item['in_amount'] ?? 0, 2),
                        number_format($item['total_available_quantity'] ?? 0, 2),
                        number_format($item['total_available_amount'] ?? 0, 2),
                        number_format($item['out_quantity'] ?? 0, 2),
                        number_format($item['out_amount'] ?? 0, 2),
                        number_format($item['balance_quantity'] ?? 0, 2),
                        number_format($item['balance_amount'] ?? 0, 2),
                        '0',
                        '-',
                    ]);
                } catch (\Exception $e) {
                    // Skip this item but continue processing others
                }
            });

            // Calculate totals from the collection
            $totals = [
                'Totals', '', '',
                number_format($itemsCollection->sum('opening_quantity') ?? 0, 2),
                '-',
                number_format($itemsCollection->sum('opening_amount') ?? 0, 2),
                number_format($itemsCollection->sum('in_quantity') ?? 0, 2),
                number_format($itemsCollection->sum('in_amount') ?? 0, 2),
                number_format($itemsCollection->sum('total_available_quantity') ?? 0, 2),
                number_format($itemsCollection->sum('total_available_amount') ?? 0, 2),
                number_format($itemsCollection->sum('out_quantity') ?? 0, 2),
                number_format($itemsCollection->sum('out_amount') ?? 0, 2),
                number_format($itemsCollection->sum('balance_quantity') ?? 0, 2),
                number_format($itemsCollection->sum('balance_amount') ?? 0, 2),
                '-',
                '-'
            ];

            // Add totals row
            $data->push($totals);

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function headings(): array
    {
        return [
            [
                'Code',
                'Item',
                'Unit',
                'Opening Balance',
                '',
                '',
                'IN',
                '',
                'Total Available',
                '',
                'OUT',
                '',
                'Balance',
                '',
                'Additional',
                ''
            ],
            [
                '',
                '',
                '',
                'Quant',
                'Unit Cost',
                'Amount',
                'Quant',
                'Amount',
                'Quant',
                'Amount',
                'Quant',
                'Amount',
                'Quant',
                'Amount',
                'Act',
                'Diff'
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        try {
            // Get the last row number (add 3 to account for header rows and 0-based index)
            $lastRow = (is_array($this->items) ? count($this->items) : 0) + 3;

            // Style for headers
            $sheet->getStyle('A1:P2')->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin']
                ]
            ]);

            // Merge cells for first row headers
            $sheet->mergeCells('A1:A2');
            $sheet->mergeCells('B1:B2');
            $sheet->mergeCells('C1:C2');
            $sheet->mergeCells('D1:F1');  // Opening Balance
            $sheet->mergeCells('G1:H1');  // IN
            $sheet->mergeCells('I1:J1');  // Total Available
            $sheet->mergeCells('K1:L1');  // OUT
            $sheet->mergeCells('M1:N1');  // Balance
            $sheet->mergeCells('O1:P1');  // Additional

            // Color coding for sections
            $sheet->getStyle('D1:F2')->getFill()->setFillType('solid')->getStartColor()->setRGB('87CEEB');  // Opening Balance
            $sheet->getStyle('G1:H2')->getFill()->setFillType('solid')->getStartColor()->setRGB('98FB98');  // IN
            $sheet->getStyle('I1:J2')->getFill()->setFillType('solid')->getStartColor()->setRGB('F0E68C');  // Total Available
            $sheet->getStyle('K1:L2')->getFill()->setFillType('solid')->getStartColor()->setRGB('FFB6C1');  // OUT
            $sheet->getStyle('M1:N2')->getFill()->setFillType('solid')->getStartColor()->setRGB('98FB98');  // Balance
            $sheet->getStyle('O1:P2')->getFill()->setFillType('solid')->getStartColor()->setRGB('D3D3D3');  // Additional

            // Add formula to column P for each row
            for($row = 3; $row <= $lastRow; $row++) {
                $sheet->setCellValue("P{$row}", "=M{$row}-O{$row}");
            }

            // Add conditional formatting for column P
            $conditionalStyles = $sheet->getStyle("P3:P{$lastRow}")->getConditionalStyles();
            $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
            $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
            $conditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_GREATERTHAN);
            $conditional->addCondition(0);
            $conditional->getStyle()->getFont()->getColor()->setRGB('FF0000');
            $conditionalStyles[] = $conditional;
            $sheet->getStyle("P3:P{$lastRow}")->setConditionalStyles($conditionalStyles);

            // Set number format for numeric columns
            $numericColumns = ['D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];
            foreach ($numericColumns as $column) {
                $sheet->getStyle("{$column}3:{$column}{$lastRow}")->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            }

            // Alignment for all cells
            $sheet->getStyle('A3:P' . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => 'right'],
                'borders' => [
                    'allBorders' => ['borderStyle' => 'thin']
                ]
            ]);
            
            // Left align text columns
            $sheet->getStyle('A3:C' . $lastRow)->applyFromArray([
                'alignment' => ['horizontal' => 'left'],
            ]);

            // Style for totals row
            $sheet->getStyle('A' . $lastRow . ':P' . $lastRow)->applyFromArray([
                'font' => ['bold' => true],
                'borders' => [
                    'top' => ['borderStyle' => 'medium'],
                    'bottom' => ['borderStyle' => 'medium']
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => 'F0F0F0']
                ]
            ]);

            // Conditional formatting for negative values
            foreach (['M', 'N'] as $column) {
                $conditional = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $conditional->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
                $conditional->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_LESSTHAN);
                $conditional->addCondition(0);
                $conditional->getStyle()->applyFromArray([
                    'font' => ['color' => ['rgb' => 'FF0000']]
                ]);

                $conditionalStyles = $sheet->getStyle("{$column}3:{$column}{$lastRow}")->getConditionalStyles();
                $conditionalStyles[] = $conditional;
                $sheet->getStyle("{$column}3:{$column}{$lastRow}")->setConditionalStyles($conditionalStyles);
            }

        } catch (\Exception $e) {
            \Log::error('Error in ItemReportExport styles()', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Code
            'B' => 30,  // Item
            'C' => 10,  // Unit
            'D' => 12,  // Opening Quant
            'E' => 12,  // Opening Unit Cost
            'F' => 15,  // Opening Amount
            'G' => 12,  // IN Quant
            'H' => 15,  // IN Amount
            'I' => 12,  // Available Quant
            'J' => 15,  // Available Amount
            'K' => 12,  // OUT Quant
            'L' => 15,  // OUT Amount
            'M' => 12,  // Balance Quant
            'N' => 15,  // Balance Amount
            'O' => 10,  // Act
            'P' => 10,  // Diff
        ];
    }
}
