<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupplierReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $receivings;
    protected $supplierName;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($receivings, $supplierName, $dateFrom, $dateTo)
    {
        $this->receivings = $receivings;
        $this->supplierName = $supplierName;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function collection()
    {
        return $this->receivings;
    }

    public function title(): string
    {
        return __('messages.supplier_report');
    }

    public function headings(): array
    {
        return [
            __('messages.receipt_number'),
            __('messages.item'),
            __('messages.department'),
            __('messages.quantity'),
            __('messages.unit'),
            __('messages.unit_price'),
            __('messages.total'),
            __('messages.date'),
        ];
    }

    public function map($receiving): array
    {
        return [
            $receiving->receiving_number ?? 'N/A',
            $receiving->item->name ?? 'N/A',
            $receiving->department->name ?? 'N/A',
            number_format($receiving->quantity, 2),
            $receiving->unit->name ?? 'N/A',
            number_format(($receiving->unit_price ?? $receiving->price ?? 0), 2),
            number_format((($receiving->unit_price ?? $receiving->price ?? 0) * $receiving->quantity), 2),
            $receiving->received_at ? \Carbon\Carbon::parse($receiving->received_at)->format('Y-m-d') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Make first row bold
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        
        // Add title and date range
        $sheet->insertNewRowBefore(1, 3);
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', __('messages.supplier_report') . ': ' . $this->supplierName);
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', __('messages.date_from') . ': ' . $this->dateFrom . ' ' . __('messages.date_to') . ': ' . $this->dateTo);
        
        // Style the title and date range
        $sheet->getStyle('A1:H1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:H2')->getFont()->setBold(true);
        
        // Adjust row height
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(20);
        
        // Auto-size all columns
        foreach(range('A','H') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}
