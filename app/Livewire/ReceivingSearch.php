<?php

namespace App\Livewire;

use App\Livewire\ItemSearchTrait;
use Livewire\Component;
use App\Models\Receiving;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Department;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class ReceivingSearch extends Component
{
    use ItemSearchTrait;

    public $searchNumbers = '';
    public $receivings = [];
    public $totalSubtotal = 0;
    public $totalTax = 0;
    public $totalDiscount = 0;
    public $grandTotal = 0;

    // Search receiving variables
    public $searchReceivingNumber = '';
    public $receivingItems = [];
    public $date;
    public $selectedDepartmentId;
    public $selectedSupplierId;
    public $departments = [];
    public $suppliers = [];
    public $units = [];
    public $items = [];
    public $editingItemId = null;
    public $editingItem = [];

    protected $rules = [
        'editingItem.quantity' => 'required|numeric|min:0',
        'editingItem.unit_id' => 'required|exists:units,id',
        'editingItem.unit_price' => 'required|numeric|min:0',
        'editingItem.item_id' => 'required|exists:items,id'
    ];

    public function mount()
    {
        $this->receivings = [];
        $this->departments = Department::all()->toArray();
        $this->suppliers = Supplier::all()->toArray();
        $this->units = Unit::all()->toArray();
        $this->items = Item::all(['id', 'name'])->toArray();
        $this->date = now()->toDateString();
    }

    public function updatedItemSearch()
    {
        $this->items = $this->searchItems($this->itemSearch, 10);
    }

    // Multiple receiving search
    public function search()
    {
        if (empty($this->searchNumbers)) {
            return;
        }

        $numbers = array_map('trim', explode(',', $this->searchNumbers));
        
        $this->receivings = Receiving::whereIn('receiving_number', $numbers)
            ->with(['item', 'supplier', 'department', 'unit'])
            ->get()
            ->groupBy('receiving_number')
            ->map(function ($items) {
                $total = $items->sum('quantity');
                $date = $items->first()->received_at;
                return [
                    'items' => $items,
                    'total_quantity' => $total,
                    'date' => $date,
                    'department' => $items->first()->department,
                    'supplier' => $items->first()->supplier
                ];
            })
            ->toArray();

        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->totalSubtotal = 0;
        $this->totalTax = 0;
        $this->totalDiscount = 0;

        foreach ($this->receivings as $receivingNumber => $receivingGroup) {
            foreach ($receivingGroup['items'] as $receiving) {
                $subtotal = $receiving->quantity * $receiving->unit_price;
                $this->totalSubtotal += $subtotal;
                $this->totalTax += $subtotal * ($receiving->tax / 100);
                $this->totalDiscount += $subtotal * ($receiving->discount / 100);
            }
        }

        $this->grandTotal = $this->totalSubtotal + $this->totalTax - $this->totalDiscount;
    }

    // Single receiving search
    public function searchReceiving()
    {
        if (empty($this->searchReceivingNumber)) {
            $this->receivingItems = [];
            return;
        }

        $receivings = Receiving::where('receiving_number', $this->searchReceivingNumber)
            ->with(['item', 'supplier', 'department', 'unit'])
            ->get();

        if ($receivings->isEmpty()) {
            session()->flash('error', 'No receiving found with this number.');
            $this->receivingItems = [];
            return;
        }

        $firstReceiving = $receivings->first();
        $this->date = date('Y-m-d', strtotime($firstReceiving->received_at));
        $this->selectedDepartmentId = $firstReceiving->department_id;
        $this->selectedSupplierId = $firstReceiving->supplier_id;

        $this->receivingItems = $receivings->map(function ($receiving) {
            return [
                'id' => $receiving->id,
                'item_id' => $receiving->item_id,
                'name' => $receiving->item->name,
                'quantity' => $receiving->quantity,
                'unit_price' => $receiving->unit_price,
                'unit_id' => $receiving->unit_id,
                'unit_name' => $receiving->unit->name,
                'tax' => $receiving->tax,
                'discount' => $receiving->discount
            ];
        })->toArray();
    }

    public function editItem($itemId)
    {
        $this->editingItemId = $itemId;
        $item = collect($this->receivingItems)->firstWhere('id', $itemId);
        $this->editingItem = [
            'quantity' => $item['quantity'],
            'unit_id' => $item['unit_id'],
            'unit_price' => $item['unit_price'],
            'item_id' => $item['item_id']
        ];
    }

    public function cancelEdit()
    {
        $this->editingItemId = null;
        $this->editingItem = [];
    }

    public function saveItemChanges()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // Update the receiving record
            Receiving::where('id', $this->editingItemId)->update([
                'quantity' => $this->editingItem['quantity'],
                'unit_id' => $this->editingItem['unit_id'],
                'unit_price' => $this->editingItem['unit_price'],
                'item_id' => $this->editingItem['item_id']
            ]);

            // Get the new item details
            $newItem = Item::find($this->editingItem['item_id']);

            // Update the local array
            $index = collect($this->receivingItems)->search(function($item) {
                return $item['id'] === $this->editingItemId;
            });

            if ($index !== false) {
                $this->receivingItems[$index]['quantity'] = $this->editingItem['quantity'];
                $this->receivingItems[$index]['unit_id'] = $this->editingItem['unit_id'];
                $this->receivingItems[$index]['unit_price'] = $this->editingItem['unit_price'];
                $this->receivingItems[$index]['item_id'] = $this->editingItem['item_id'];
                $this->receivingItems[$index]['name'] = $newItem->name;
            }

            DB::commit();
            session()->flash('message', 'Item updated successfully!');
            $this->editingItemId = null;
            $this->editingItem = [];

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating item: ' . $e->getMessage());
        }
    }

    public function updateReceiving()
    {
        $this->validate([
            'date' => 'required|date',
            'selectedDepartmentId' => 'required|exists:departments,id',
            'selectedSupplierId' => 'required|exists:suppliers,id',
            'receivingItems.*.quantity' => 'required|numeric|min:0',
            'receivingItems.*.unit_price' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            foreach ($this->receivingItems as $item) {
                Receiving::where('id', $item['id'])->update([
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'department_id' => $this->selectedDepartmentId,
                    'supplier_id' => $this->selectedSupplierId,
                    'received_at' => $this->date
                ]);
            }

            DB::commit();
            session()->flash('message', 'Receiving updated successfully!');
            $this->searchReceiving(); // Refresh the data
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating receiving: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.receiving-search')->layout('layouts.app');
    }
}