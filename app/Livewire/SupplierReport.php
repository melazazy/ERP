<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Receiving;
use App\Exports\SupplierReportExport;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class SupplierReport extends Component
{
    use WithPagination;

    public $selectedSupplier;
    public $dateFrom;
    public $dateTo;
    public $suppliers;
    public $receivings = [];
    public $totalAmount = 0;
    public $docNumber;
    public $supplierName = '';

    public function mount()
    {
        $this->suppliers = \App\Models\Supplier::all();
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSelectedSupplier()
    {
        $this->generateReport();
    }

    public function updatedDateFrom()
    {
        if ($this->selectedSupplier) {
            $this->generateReport();
        }
    }

    public function updatedDateTo()
    {
        if ($this->selectedSupplier) {
            $this->generateReport();
        }
    }

    public function updatedDocNumber()
    {
        if ($this->selectedSupplier) {
            $this->generateReport();
        }
    }

    public function exportToExcel()
    {
        if (!$this->selectedSupplier) {
            return;
        }

        $supplier = Supplier::find($this->selectedSupplier);
        $this->supplierName = $supplier->name;
        $fileName = 'supplier_report_' . $this->supplierName . '_' . $this->dateFrom . '_to_' . $this->dateTo . '.xlsx';
        
        return Excel::download(
            new SupplierReportExport($this->receivings, $this->supplierName, $this->dateFrom, $this->dateTo),
            $fileName
        );
    }

    public function generateReport()
    {
        if (!$this->selectedSupplier) {
            $this->receivings = [];
            $this->totalAmount = 0;
            return;
        }

        $supplier = Supplier::find($this->selectedSupplier);
        $this->supplierName = $supplier->name;

        $query = Receiving::with(['item', 'unit', 'department'])
            ->where('supplier_id', $this->selectedSupplier)
            ->whereBetween('received_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ]);

        if ($this->docNumber) {
            $query->where('receiving_number', 'like', '%' . $this->docNumber . '%');
        }

        $this->receivings = $query->get();
        $this->totalAmount = $this->receivings->sum(function($receiving) {
            return ($receiving->unit_price ?? $receiving->price ?? 0) * $receiving->quantity;
        });
    }

    public function render()
    {
        return view('livewire.supplier-report')
            ->layout('layouts.app');
    }
}
