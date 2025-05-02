<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;
use App\Models\Transfer;
use Carbon\Carbon;

class DocumentSearch extends Component
{
    public $documentType = 'receiving';
    public $fromDate = null;
    public $toDate = null;
    public $documents = [];

    protected $queryString = [
        'documentType',
        'fromDate',
        'toDate'
    ];

    public function mount()
    {
        $this->loadLatestDocuments();
        $this->fromDate = now()->subMonth()->toDateString();
        $this->toDate = now()->toDateString();
    }

    public function loadLatestDocuments()
    {
        $receiving = Receiving::query()
            ->selectRaw('
                receiving_number,
                MAX(received_at) as received_at,
                SUM(quantity * unit_price) as subtotal,
                SUM(quantity * unit_price * (tax/100)) as tax_amount,
                SUM(quantity * unit_price * (discount/100)) as discount_amount,
                SUM(quantity * unit_price) + (SUM(quantity * unit_price * (tax/100))) - (SUM(quantity * unit_price * (discount/100))) as total_amount,
                "receiving" as document_type
            ')
            ->groupBy('receiving_number')
            ->orderBy('received_at', 'desc')
            ->limit(1)
            ->first();

        $requisition = Requisition::query()
            ->selectRaw('
                requisition_number,
                requested_date,
                "requisition" as document_type
            ')
            ->distinct('requisition_number')
            ->orderBy('requested_date', 'desc')
            ->limit(1)
            ->first();

        $trust = Trust::query()
            ->selectRaw('
                requisition_number,
                requested_date,
                "trust" as document_type
            ')
            ->distinct('requisition_number')
            ->orderBy('requested_date', 'desc')
            ->limit(1)
            ->first();

        // $transfer = Transfer::query()
        //     ->select('requisition_number', 'transfer_date')
        //     ->distinct('requisition_number')
        //     ->orderBy('transfer_date', 'desc')
        //     ->limit(1)
        //     ->first();

        $this->documents = collect([$receiving, $requisition, $trust])->filter()->toArray();
    }

    public function loadDocuments()
    {
        if (!$this->fromDate || !$this->toDate) {
            $this->documents = [];
            return;
        }

        $query = match ($this->documentType) {
            'receiving' => Receiving::query()
                ->selectRaw('
                    receiving_number,
                    MAX(received_at) as received_at,
                    SUM(quantity * unit_price) as subtotal,
                    SUM(quantity * unit_price * (tax/100)) as tax_amount,
                    SUM(quantity * unit_price * (discount/100)) as discount_amount,
                    SUM(quantity * unit_price) + (SUM(quantity * unit_price * (tax/100))) - (SUM(quantity * unit_price * (discount/100))) as total_amount,
                    "receiving" as document_type
                ')
                ->whereBetween('received_at', [
                    Carbon::parse($this->fromDate)->startOfDay(),
                    Carbon::parse($this->toDate)->endOfDay()
                ])
                ->groupBy('receiving_number')
                ->orderBy('received_at', 'desc'),

            'requisition' => Requisition::query()
                ->selectRaw('
                    requisition_number,
                    requested_date,
                    "requisition" as document_type
                ')
                ->whereBetween('requested_date', [
                    Carbon::parse($this->fromDate)->startOfDay(),
                    Carbon::parse($this->toDate)->endOfDay()
                ])
                ->distinct('requisition_number')
                ->orderBy('requested_date', 'desc'),

            'trust' => Trust::query()
                ->selectRaw('
                    requisition_number,
                    requested_date,
                    "trust" as document_type
                ')
                ->whereBetween('requested_date', [
                    Carbon::parse($this->fromDate)->startOfDay(),
                    Carbon::parse($this->toDate)->endOfDay()
                ])
                ->distinct('requisition_number')
                ->orderBy('requested_date', 'desc')
        };

        $this->documents = $query->get();
    }

    public function submitSearch()
    {
        $this->loadDocuments();
    }

    public function render()
    {
        return view('livewire.document-search')->layout('layouts.app');
    }
}