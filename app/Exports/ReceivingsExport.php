<?php

namespace App\Exports;

use App\Models\Receiving;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReceivingsExport implements FromCollection, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return Receiving::with(['item', 'supplier', 'department', 'unit'])
            ->whereBetween('received_at', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($receiving) {
                return [
                    'Receiving Number' => $receiving->receiving_number,
                    'Item' => $receiving->item->name,
                    'Quantity' => $receiving->quantity,
                    'Unit' => $receiving->unit->name,
                    'Unit Price' => $receiving->unit_price,
                    'Total' => $receiving->quantity * $receiving->unit_price,
                    'Supplier' => $receiving->supplier->name,
                    'Department' => $receiving->department->name,
                    'Date' => \Carbon\Carbon::parse($receiving->received_at)->format('Y-m-d'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Receiving Number',
            'Item',
            'Quantity',
            'Unit',
            'Unit Price',
            'Total',
            'Supplier',
            'Department',
            'Date'
        ];
    }
}