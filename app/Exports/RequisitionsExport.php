<?php

namespace App\Exports;

use App\Models\Requisition;
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
use Illuminate\Support\Facades\Log;

class RequisitionsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
        Log::info('RequisitionsExport initialized', ['count' => $data->count()]);
    }

    public function collection()
    {
        return $this->data;
    }

    public function map($requisition): array
    {
        try {
            Log::debug('Processing requisition', ['id' => $requisition->id]);
            
            // Load relationships if not already loaded
            $requisition->loadMissing([
                'item',
                'department',
                'requester',
                'unit'
            ]);

            return [
                $requisition->requisition_number ?? 'N/A',
                $requisition->item->name ?? 'N/A',
                $requisition->item->code ?? 'N/A',
                $requisition->department->name ?? 'N/A',
                $requisition->requester->name ?? 'N/A',
                $requisition->quantity ?? 0,
                $requisition->unit->name ?? 'N/A',
                __('messages.' . ($requisition->status ?? 'pending')),
                $requisition->requested_date ? \Carbon\Carbon::parse($requisition->requested_date)->format('Y-m-d') : 'N/A',
            ];
        } catch (\Exception $e) {
            Log::error('Error mapping requisition', [
                'requisition_id' => $requisition->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                $requisition->requisition_number ?? 'ERROR',
                'Error',
                'Error',
                'Error',
                'Error',
                'Error',
                'Error',
                'Error',
                'Error',
            ];
        }
    }


    public function headings(): array
    {
        return [
            __('messages.requisition_number'),
            __('messages.item'),
            __('messages.item_code'),
            __('messages.department'),
            __('messages.requested_by'),
            __('messages.quantity'),
            __('messages.unit'),
            __('messages.status'),
            __('messages.requested_date'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        try {
            // Get the last row number
            $lastRow = $sheet->getHighestRow();
            
            if ($lastRow < 1) {
                Log::warning('No data rows found in the export');
                return;
            }
            
            // Style for headers (A1:I1)
            $sheet->getStyle('A1:I1')->applyFromArray([
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


            if ($lastRow > 1) {
                // Style for data cells
                $sheet->getStyle('A2:I'.$lastRow)->applyFromArray([
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

                // Quantity column right-aligned
                $sheet->getStyle('F2:F'.$lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);

                // Status column styling
                $sheet->getStyle('H2:H'.$lastRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Alternate row colors
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8F9FA'],
                            ],
                        ]);
                    }
                }
            }


            // Set row height
            $sheet->getDefaultRowDimension()->setRowHeight(20);
            $sheet->getRowDimension(1)->setRowHeight(25);

            // Auto-fit columns
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        } catch (\Exception $e) {
            Log::error('Error applying styles to export', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // Requisition Number
            'B' => 30, // Item Name
            'C' => 15, // Item Code
            'D' => 25, // Department
            'E' => 25, // Requested By
            'F' => 12, // Quantity
            'G' => 15, // Unit
            'H' => 15, // Status
            'I' => 15, // Requested Date
        ];
    }
}
