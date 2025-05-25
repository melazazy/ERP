<?php

namespace App\Livewire;

use Livewire\Component;
use App\Exports\ReceivingsExport;
use App\Exports\RequisitionsExport;
use App\Exports\TrustsExport;
use App\Models\Receiving;
use App\Models\Department;
use App\Models\Trust;
use App\Models\Requisition;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade as PDF;

class ExportReports extends Component
{
    public $startDate;
    public $endDate;
    public $reportType = 'receivings';
    public $departmentId = '';
    public $status = '';
    public $exportFormat = 'excel';
    public $departments = [];
    public $reportData = [];
    public $showReport = false;
    public $reportColumns = [];
    public $reportTitle = '';

    public function mount()
    {
        $this->departments = Department::all()->toArray();
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function generateReport()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
            'reportType' => 'required|in:receivings,requisitions,trusts',
        ]);

        try {
            $query = match($this->reportType) {
                'receivings' => $this->getReceivingsQuery(),
                'requisitions' => $this->getRequisitionsQuery(),
                'trusts' => $this->getTrustsQuery(),
            };

            if ($this->departmentId) {
                $query->where('department_id', $this->departmentId);
            }

            if ($this->status) {
                $query->where('status', $this->status);
            }

            $this->reportData = $query->get();
            $this->setReportColumns();
            $this->showReport = true;

        } catch (\Exception $e) {
            $this->showReport = false;
            session()->flash('error', __('messages.error_generating_report') . ': ' . $e->getMessage());
        }
    }

    protected function setReportColumns()
    {
        $this->reportColumns = match($this->reportType) {
            'receivings' => [
                'receipt_number' => __('messages.receipt_number'),
                'item' => __('messages.item'),
                'supplier' => __('messages.supplier'),
                'department' => __('messages.department'),
                'quantity' => __('messages.quantity'),
                'unit' => __('messages.unit'),
                'price' => __('messages.price'),
                'total' => __('messages.total'),
                'received_at' => __('messages.received_at'),
            ],
            'requisitions' => [
                'requisition_number' => __('messages.requisition_number'),
                'item' => __('messages.item'),
                'item_code' => __('messages.item_code'),
                'department' => __('messages.department'),
                'requested_by' => __('messages.requested_by'),
                'quantity' => __('messages.quantity'),
                'unit' => __('messages.unit'),
                'status' => __('messages.status'),
                'requested_date' => __('messages.requested_date'),
            ],
            'trusts' => [
                'requisition_number' => __('messages.requisition_number'),
                'item' => __('messages.item'),
                'item_code' => __('messages.item_code'),
                'quantity' => __('messages.quantity'),
                'department' => __('messages.department'),
                'requested_by' => __('messages.requested_by'),
                'status' => __('messages.status'),
                'requested_date' => __('messages.requested_date'),
            ],
        };

        $this->reportTitle = match($this->reportType) {
            'receivings' => __('messages.receivings_report'),
            'requisitions' => __('messages.requisitions_report'),
            'trusts' => __('messages.trusts_report'),
        };
    }

    public function exportReport()
    {
        if (empty($this->reportData)) {
            return;
        }

        try {
            $export = match($this->reportType) {
                'receivings' => new ReceivingsExport($this->reportData),
                'requisitions' => new RequisitionsExport($this->reportData),
                'trusts' => new TrustsExport($this->reportData),
            };

            if ($this->exportFormat === 'excel') {
                return Excel::download(
                    $export,
                    $this->reportType . '_' . now()->format('Y-m-d') . '.xlsx'
                );
            } else {
                $pdf = PDF::loadView('exports.pdf', [
                    'data' => $this->reportData,
                    'columns' => $this->reportColumns,
                    'title' => $this->reportTitle,
                ]);
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, $this->reportType . '_' . now()->format('Y-m-d') . '.pdf');
            }
        } catch (\Exception $e) {
            session()->flash('error', __('messages.error_exporting_report') . ': ' . $e->getMessage());
        }
    }

    protected function getReceivingsQuery()
    {
        return Receiving::with(['item', 'supplier', 'department', 'unit'])
            ->whereBetween('received_at', [$this->startDate, $this->endDate]);
    }

    protected function getRequisitionsQuery()
    {
        return Requisition::with(['item', 'department', 'requester', 'unit'])
            ->whereBetween('requested_date', [$this->startDate, $this->endDate]);
    }

    protected function getTrustsQuery()
    {
        return Trust::with(['item', 'department', 'user'])
            ->whereBetween('requested_date', [$this->startDate, $this->endDate]);
    }

    public function render()
    {
        return view('livewire.export-reports')->layout('layouts.app');
    }
}