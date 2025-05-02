<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Receiving;
use App\Models\Requisition;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class DepartmentReport extends Component
{
    use WithPagination;

    public $selectedDepartment;
    public $dateFrom;
    public $dateTo;
    public $departments;
    public $receivings = [];
    public $requisitions = [];
    public $totalReceivings = 0;
    public $totalRequisitions = 0;
    public $selectedDocType = 'all'; // 'all', 'receiving', or 'requisition'
    public $docNumber;

    public function mount()
    {
        $this->departments = Department::all();
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedSelectedDepartment()
    {
        $this->generateReport();
    }

    public function updatedDateFrom()
    {
        if ($this->selectedDepartment) {
            $this->generateReport();
        }
    }

    public function updatedDateTo()
    {
        if ($this->selectedDepartment) {
            $this->generateReport();
        }
    }

    public function updatedSelectedDocType()
    {
        if ($this->selectedDepartment) {
            $this->generateReport();
        }
    }

    public function updatedDocNumber()
    {
        if ($this->selectedDepartment) {
            $this->generateReport();
        }
    }

    public function generateReport()
    {
        if (!$this->selectedDepartment) {
            $this->receivings = [];
            $this->requisitions = [];
            $this->totalReceivings = 0;
            $this->totalRequisitions = 0;
            return;
        }

        if ($this->selectedDocType === 'all' || $this->selectedDocType === 'receiving') {
            $query = Receiving::with(['item', 'supplier'])
                ->where('department_id', $this->selectedDepartment)
                ->whereBetween('received_at', [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay()
                ]);

            if ($this->docNumber) {
                $query->where('receiving_number', $this->docNumber);
            }

            $this->receivings = $query->get();
            $this->totalReceivings = $this->receivings->sum('quantity');
        } else {
            $this->receivings = [];
            $this->totalReceivings = 0;
        }

        if ($this->selectedDocType === 'all' || $this->selectedDocType === 'requisition') {
            $query = Requisition::with(['item', 'requester'])
                ->where('department_id', $this->selectedDepartment)
                ->whereBetween('requested_date', [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay()
                ]);

            if ($this->docNumber) {
                $query->where('requisition_number', $this->docNumber);
            }

            $this->requisitions = $query->get();
            $this->totalRequisitions = $this->requisitions->sum('quantity');
        } else {
            $this->requisitions = [];
            $this->totalRequisitions = 0;
        }
    }

    public function render()
    {
        return view('livewire.department-report')->layout('layouts.app');
    }
}