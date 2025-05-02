<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\Trust;
use App\Models\Receiving;
use App\Models\Item;

class Reports extends Component
{
    public $requisitions;
    public $trusts;
    public $receivings;
    public $selectedItem;
    public $itemMovements = [];
    public $itemSearch = '';
    public $items = [];

    public function mount()
    {
        $this->fetchReports();
    }

    public function fetchReports()
    {
        $this->requisitions = Requisition::with(['item', 'department', 'requester'])->get();
        $this->trusts = Trust::with(['item', 'department', 'requester'])->get();
        $this->receivings = Receiving::with(['item', 'supplier', 'department'])->get();
        // dd($this->receivings);
    }

    public function updatedItemSearch()
    {
        if (empty($this->itemSearch)) {
            $this->items = [];
        } else {
            $this->items = Item::where('name', 'like', '%' . $this->itemSearch . '%')
                ->orWhere('code', 'like', '%' . $this->itemSearch . '%')
                ->limit(10)
                ->get();
        }
    }

    public function selectItem($itemId)
    {
        $this->selectedItem = $itemId;
        $this->getItemMovements();
        $this->itemSearch = Item::find($itemId)->name; // Set the search input to the selected item name
        $this->items = []; // Clear the search results
    }

    public function getItemMovements()
    {
        if ($this->selectedItem) {
            $item = Item::find($this->selectedItem);
            
            // Get all movements from different models
            $receivings = $item->receivings->map(function ($receiving) {
                return [
                    'date' => $receiving->received_at,
                    'type' => 'in',
                    'quantity' => $receiving->quantity,
                    'unit_price' => $receiving->unit_price,
                    'document_number' => $receiving->receiving_number,
                    'tax' => $receiving->tax ?? 0,
                    'discount' => $receiving->discount ?? 0,
                    'total' => ($receiving->quantity * $receiving->unit_price) * (1 + ($receiving->tax ?? 0)/100) * (1 - ($receiving->discount ?? 0)/100),
                    'description' => 'استلام من ' . $receiving->supplier->name
                ];
            });

            $requisitions = $item->requisitions->map(function ($requisition) {
                return [
                    'date' => $requisition->created_at->toDateString(),
                    'type' => 'out',
                    'quantity' => $requisition->quantity,
                    'unit_price' => 0,
                    'document_number' => $requisition->requisition_number,
                    'tax' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'description' => 'صرف إلى ' . $requisition->department->name
                ];
            });

            $trusts = $item->trusts->map(function ($trust) {
                return [
                    'date' => $trust->created_at->toDateString(),
                    'type' => 'out',
                    'quantity' => $trust->quantity,
                    'unit_price' => 0,
                    'document_number' => $trust->trust_number ?? '-',
                    'tax' => 0,
                    'discount' => 0,
                    'total' => 0,
                    'description' => 'عهدة إلى ' . $trust->requester->name
                ];
            });

            // Combine all movements and sort by date
            $this->itemMovements = collect()
                ->concat($receivings)
                ->concat($requisitions)
                ->concat($trusts)
                ->sortBy([
                    ['date', 'asc'],
                    ['type', 'asc']
                ])
                ->values()
                ->toArray();
        }
    }

    public function render()
    {
        return view('livewire.reports', [
            'items' => Item::all()
        ])->layout('layouts.app');
    }
}