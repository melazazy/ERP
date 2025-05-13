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
    public $showDeleteConfirmation = false;
    public $showDeleteItemConfirmation = null;
    public $currentReceivingNumber;
    public $itemSearch = '';
    public $searchedItems = [];
    public $selectedItemId = null;

    protected $rules = [
        'editingItem.quantity' => 'required|numeric|min:0',
        'editingItem.unit_id' => 'required|exists:units,id',
        'editingItem.unit_price' => 'required|numeric|min:0',
        'editingItem.item_id' => 'required|exists:items,id',
        'editingItem.department_id' => 'required|exists:departments,id',
        'editingItem.supplier_id' => 'required|exists:suppliers,id',
        'date' => 'required|date',
    ];

    public function mount()
    {
        $this->departments = Department::all()->toArray();
        $this->suppliers = Supplier::all()->toArray();
        $this->units = Unit::all()->toArray();
        $this->items = Item::with(['subcategory', 'department', 'subcategory.category'])->get()->toArray();
        $this->date = now()->toDateString();
    }

    public function updatedItemSearch($value)
    {
        if (empty($value)) {
            $this->searchedItems = [];
            return;
        }
        
        $this->searchedItems = $this->searchItems($value, 10);
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
        if ($this->searchReceivingNumber) {
            $items = Receiving::where('receiving_number', $this->searchReceivingNumber)
                ->with(['item', 'department', 'supplier', 'unit'])
                ->get();
        
            $this->receivingItems = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item' => $item->item ? $item->item->toArray() : null,
                    'item_id' => $item->item_id,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit ? $item->unit->toArray() : null,
                    'unit_id' => $item->unit_id,
                    'unit_price' => $item->unit_price,
                    'department' => $item->department ? $item->department->toArray() : null,
                    'supplier' => $item->supplier ? $item->supplier->toArray() : null,
                    'receiving_date' => $item->received_at ?: now()->toDateString(),
                    'note' => $item->note,
                ];
            })->toArray();
        
            if (!empty($this->receivingItems)) {
                $this->currentReceivingNumber = $this->searchReceivingNumber;
                $firstItem = $this->receivingItems[0];
                $this->date = $firstItem['receiving_date'] ? date('Y-m-d', strtotime($firstItem['receiving_date'])) : now()->toDateString();
                $this->selectedDepartmentId = $firstItem['department']['id'] ?? null;
                $this->selectedSupplierId = $firstItem['supplier']['id'] ?? null;
            }
        } else {
            $this->receivingItems = [];
            $this->date = now()->toDateString();
            $this->selectedDepartmentId = null;
            $this->selectedSupplierId = null;
            $this->currentReceivingNumber = null;
        }
        $this->editingItemId = null;
        $this->editingItem = [];
    }

    public function updateDateAndDepartment()
    {
        if (!$this->currentReceivingNumber) {
            session()->flash('error', 'No receiving selected.');
            return;
        }

        $validated = $this->validate([
            'date' => 'required|date',
            'selectedDepartmentId' => 'required|exists:departments,id',
            'selectedSupplierId' => 'required|exists:suppliers,id',
        ]);

        Receiving::where('receiving_number', $this->currentReceivingNumber)
            ->update([
                'received_at' => $validated['date'],
                'department_id' => $validated['selectedDepartmentId'],
                'supplier_id' => $validated['selectedSupplierId']
            ]);

        $this->searchReceiving();
        session()->flash('message', 'Date, department, and supplier updated successfully.');
    }

    public function addNewItem()
    {
        if (!$this->currentReceivingNumber) {
            session()->flash('error', 'No receiving selected.');
            return;
        }

        if (!$this->selectedItemId) {
            session()->flash('error', 'Please select an item to add.');
            return;
        }

        // Check if item already exists in the receiving
        $existingItem = Receiving::where('receiving_number', $this->currentReceivingNumber)
            ->where('item_id', $this->selectedItemId)
            ->first();

        if ($existingItem) {
            session()->flash('error', 'This item already exists in the receiving.');
            return;
        }

        // Get the first item to copy some data
        $firstItem = Receiving::where('receiving_number', $this->currentReceivingNumber)->first();

        if (!$firstItem) {
            session()->flash('error', 'Invalid receiving.');
            return;
        }

        // Create new receiving item
        Receiving::create([
            'item_id' => $this->selectedItemId,
            'department_id' => $this->selectedDepartmentId,
            'supplier_id' => $this->selectedSupplierId,
            'quantity' => 1, // Default quantity, can be edited
            'unit_id' => 1, // Default unit, should be set based on item
            'unit_price' => 0, // Default price, should be set by user
            'received_at' => $this->date,
            'receiving_number' => $this->currentReceivingNumber,
            'note' => 'Added via update',
        ]);

        $this->selectedItemId = null;
        $this->itemSearch = '';
        $this->searchReceiving();
        session()->flash('message', 'Item added to receiving successfully.');
    }

    public function removeItem($itemId)
    {
        $receivingItem = Receiving::find($itemId);
        if ($receivingItem) {
            $receivingItem->delete();
            $this->searchReceiving();
            $this->showDeleteItemConfirmation = null;
            session()->flash('message', 'Item removed successfully.');
        } else {
            session()->flash('error', 'Item not found.');
        }
    }

    public function removeAllItems()
    {
        if (!$this->currentReceivingNumber) {
            session()->flash('error', 'No receiving selected.');
            return;
        }

        Receiving::where('receiving_number', $this->currentReceivingNumber)->delete();
        $this->receivingItems = [];
        $this->currentReceivingNumber = null;
        $this->searchReceivingNumber = '';
        $this->date = now()->toDateString();
        $this->selectedDepartmentId = null;
        $this->selectedSupplierId = null;
        
        session()->flash('message', 'All items have been removed from the receiving.');
    }

    public function editItem($itemId)
    {
        $this->editingItemId = $itemId;
        $item = collect($this->receivingItems)->firstWhere('id', $itemId);
        
        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        $this->editingItem = [
            'quantity' => $item['quantity'] ?? 1,
            'unit_id' => $item['unit']['id'] ?? null,
            'unit_price' => $item['unit_price'] ?? 0,
            'item_id' => $item['item_id'] ?? ($item['item']['id'] ?? null)
        ];
    }

    public function cancelEdit()
    {
        $this->editingItemId = null;
        $this->editingItem = [];
    }

    public function saveItemChanges()
    {
        $this->validate([
            'editingItem.quantity' => 'required|numeric|min:0',
            'editingItem.unit_id' => 'required|exists:units,id',
            'editingItem.unit_price' => 'required|numeric|min:0',
            'editingItem.item_id' => 'required|exists:items,id',
        ]);
    
        try {
            DB::beginTransaction();
    
            // Update the receiving record
            $receiving = Receiving::find($this->editingItemId);
            if (!$receiving) {
                throw new \Exception('Receiving record not found');
            }
    
            $receiving->update([
                'quantity' => $this->editingItem['quantity'],
                'unit_id' => $this->editingItem['unit_id'],
                'unit_price' => $this->editingItem['unit_price'],
                'item_id' => $this->editingItem['item_id']
            ]);
    
            // Update the local array
            $index = array_search($this->editingItemId, array_column($this->receivingItems, 'id'));
            if ($index !== false) {
                $this->receivingItems[$index]['quantity'] = $this->editingItem['quantity'];
                $this->receivingItems[$index]['unit_id'] = $this->editingItem['unit_id'];
                $this->receivingItems[$index]['unit'] = Unit::find($this->editingItem['unit_id'])->toArray();
                $this->receivingItems[$index]['unit_price'] = $this->editingItem['unit_price'];
                $this->receivingItems[$index]['item_id'] = $this->editingItem['item_id'];
                $this->receivingItems[$index]['item'] = Item::find($this->editingItem['item_id'])->toArray();
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

    public function selectItem($itemId)
    {
        $this->selectedItemId = $itemId;
        $selectedItem = collect($this->items)->firstWhere('id', $itemId);
        if ($selectedItem) {
            $this->itemSearch = $selectedItem['name'];
            $this->searchedItems = [];
        }
    }

    public function selectFirstItem()
    {
        if (!empty($this->searchedItems)) {
            $firstItem = $this->searchedItems->first();
            $this->selectItem($firstItem['id']);
        }
    }

    public function render()
    {
        return view('livewire.receiving-search')->layout('layouts.app');
    }
}