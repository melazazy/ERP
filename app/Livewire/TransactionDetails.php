<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust; // Assuming you have a Trust model

class TransactionDetails extends Component
{
    public $transactionType;
    public $transactionNumber;
    public $transactionData = [];
    public $items = [];
    public $totalSubtotal = 0;
    public $totalTax = 0;
    public $totalDiscount = 0;
    public $grandTotal = 0;

    public function mount($type, $number)
    {
        $this->transactionType = $type;
        $this->transactionNumber = $number;
        $this->loadTransactionData();
    }

    public function loadTransactionData()
    {
        switch ($this->transactionType) {
            case 'receiving':
                $this->loadReceivingData();
                break;
            case 'requisition':
                $this->loadRequisitionData();
                break;
            case 'trust':
                $this->loadTrustData();
                break;
            default:
                session()->flash('error', 'Invalid transaction type.');
                break;
        }
    }

    private function loadReceivingData()
    {
        $items = Receiving::where('receiving_number', $this->transactionNumber)
            ->with(['item', 'department', 'supplier', 'unit'])
            ->get();

        if ($items->isEmpty()) {
            session()->flash('error', 'Receiving not found.');
            return;
        }

        $this->items = $items->toArray();
        $this->transactionData = [
            'date' => $items->first()->received_at,
            'department' => $items->first()->department,
            'supplier' => $items->first()->supplier,
        ];

        // Calculate totals
        $this->totalSubtotal = 0;
        $this->totalTax = 0;
        $this->totalDiscount = 0;

        foreach ($items as $item) {
            $subtotal = $item->quantity * $item->unit_price;
            $this->totalSubtotal += $subtotal;
            $this->totalTax += $subtotal * ($item->tax / 100);
            $this->totalDiscount += $subtotal * ($item->discount / 100);
        }

        $this->grandTotal = $this->totalSubtotal + $this->totalTax - $this->totalDiscount;
    }

    private function loadRequisitionData()
    {
        $items = Requisition::where('requisition_number', $this->transactionNumber)
            ->with(['item', 'department', 'unit'])
            ->get();

        if ($items->isEmpty()) {
            session()->flash('error', 'Requisition not found.');
            return;
        }

        $this->items = $items->toArray();
        $this->transactionData = [
            'date' => $items->first()->requested_date,
            'department' => $items->first()->department,
        ];
    }

    private function loadTrustData()
    {
        $items = Trust::where('requisition_number', $this->transactionNumber)
            ->with(['item', 'department', 'requester'])
            ->get();

        if ($items->isEmpty()) {
            session()->flash('error', 'Trust not found.');
            return;
        }

        $this->items = $items->toArray();
        $this->transactionData = [
            'date' => $items->first()->requested_date,
            'department' => $items->first()->department,
            'employee' => $items->first()->requester,
        ];
    }

    public function render()
    {
        return view('livewire.transaction-details')->layout('layouts.app');
    }
}