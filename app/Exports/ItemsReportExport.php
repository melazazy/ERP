<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ItemsReportExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $items;

    public function __construct($items)
    {
        // Debug: Log the first item to storage/logs/laravel.log
        // \Log::info('Export items sample:', [$items[0] ?? 'EMPTY']);
        // dd($items);
        $this->items = $items;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->items as $item) {
            $balance = $item['balance'];
            if ($balance === null || $balance === '') {
                $balance = 0;
            }
            $total_out = ($item['total_requisitions'] ?? 0) + ($item['total_trusts'] ?? 0);
            $data->push([
                $item['name'],
                $item['main_department'],
                $item['other_departments'],
                $item['total_receivings'],
                $item['avg_receiving_price'],
                $item['total_receivings_value'],
                $item['total_requisitions'],
                $item['total_trusts'],
                $total_out,
                $balance,
            ]);
        }

        // Optionally, add a totals row here if needed

        return $data;
    }

    public function headings(): array
    {
        return [
            [
                'الصنف',
                'القسم الرئيسي',
                'الأقسام الأخرى',
                'إجمالي الوارد',
                'متوسط سعر الوارد',
                'قيمة إجمالي الوارد',
                'الصرف',
                'العهد',
                'إجمالي المنصرف',
                'الرصيد',
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = count($this->items) + 1;

        // Style for headers
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin']
            ]
        ]);

        // Style for data cells
        $sheet->getStyle('A2:I' . $lastRow)->applyFromArray([
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => [
                'allBorders' => ['borderStyle' => 'thin']
            ]
        ]);

        // Alternate row colors
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle('A'.$row.':I'.$row)->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
            }
        }

        // Set row height
        $sheet->getDefaultRowDimension()->setRowHeight(20);
        $sheet->getRowDimension(1)->setRowHeight(25);

        // Auto-fit columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Item
            'B' => 20, // Main Department
            'C' => 30, // Other Departments
            'D' => 18, // Total Receivings
            'E' => 18, // Avg Receiving Price
            'F' => 22, // Total Receivings Value
            'G' => 18, // Total Requisitions
            'H' => 18, // Total Trusts
            'I' => 15, // Balance
        ];
    }
}