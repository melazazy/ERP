<?php

namespace App\Livewire;

use Livewire\Component;
use App\Exports\ReceivingsExport;
use App\Models\Receiving;
use Maatwebsite\Excel\Facades\Excel;

class ExportReports extends Component
{
    public $startDate;
    public $endDate;
    public $reportType = 'receivings';
    public $reportData = [];
    public $showReport = false;

    public function generateReport()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);

        $this->reportData = Receiving::with(['item', 'supplier', 'department', 'unit'])
            ->whereBetween('received_at', [$this->startDate, $this->endDate])
            ->get()
            ->map(function ($receiving) {
                return [
                    'receiving_number' => $receiving->receiving_number,
                    'item' => $receiving->item->name,
                    'quantity' => $receiving->quantity,
                    'unit' => $receiving->unit->name,
                    'unit_price' => $receiving->unit_price,
                    'total' => $receiving->quantity * $receiving->unit_price,
                    'supplier' => $receiving->supplier->name,
                    'department' => $receiving->department->name,
                    'date' => \Carbon\Carbon::parse($receiving->received_at)->format('Y-m-d'),
                ];
            });

        $this->showReport = true;
    }

    public function export()
    {
        return Excel::download(
            new ReceivingsExport($this->startDate, $this->endDate),
            $this->reportType.'_'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function render()
    {
        return view('livewire.export-reports')->layout('layouts.app');
    }
}