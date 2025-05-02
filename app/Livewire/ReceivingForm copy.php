<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Department;
use App\Models\Receiving;
use App\Models\Unit;

class ReceivingForm extends Component
{
    public $searchReceivingNumber;
    public $receivingItems = [];
    public $editMode = false;
    public $companyName;
    public $date;
    public $Receiving_num=000;
    public $searchTerm = '';
    public $selectedItems = [];
    public $items = [];
    public $subtotal = 0;
    public $tax = 0;
    public $taxRate = 14;
    public $discount = 0;
    public $discountRate = 0;
    public $total = 0;
    public $supplierSearch;
    public $departmentSearch;
    public $suppliers = [];
    public $departments = [];
    public $selectedSupplier;
    public $selectedDepartment;
    public $itemSearch;
    public $itemCode;
    public $applyTax = true; // Default to true
    public $applyDiscount = false; // Default to false
    public $units;


    public function mount()
    {
        $this->date = now()->toDateString();
        $this->departments = Department::all();
        $this->suppliers = Supplier::all();
        $this->units = Unit::all();
    }

    public function searchReceiving()
    {
        $this->validate([
            'searchReceivingNumber' => 'required|string'
        ]);

        try {
            // Get all items with this receiving number
            $receivings = Receiving::where('receiving_number', $this->searchReceivingNumber)
                ->with(['item', 'department', 'supplier'])
                ->get();

            if ($receivings->isEmpty()) {
                session()->flash('error', 'Receiving number not found.');
                return;
            }

            // Create array of items
            $this->receivingItems = $receivings->map(function($receiving) {
                $item = $receiving->item;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'quantity' => $receiving->quantity,
                    'unit_price' => $receiving->unit_price,
                    'total' => $receiving->quantity * $receiving->unit_price
                ];
            })->toArray();

            // Get the first receiving to get common data
            $firstReceiving = $receivings->first();
            $this->date = $firstReceiving->received_at ?? $firstReceiving->created_at;
            $this->selectedDepartment = $firstReceiving->department_id;
            $this->selectedSupplier = $firstReceiving->supplier_id;
            $this->editMode = true;

            session()->flash('message', 'Found ' . count($this->receivingItems) . ' items.');

        } catch (\Exception $e) {
            session()->flash('error', 'Error processing receiving: ' . $e->getMessage());
        }
    }
    public function updateReceiving()
    {
        $this->validate([
            'date' => 'required|date',
            'selectedDepartment' => 'required|exists:departments,id',
            'selectedSupplier' => 'required|exists:suppliers,id',
            'receivingItems.*.quantity' => 'required|numeric|min:1',
            'receivingItems.*.unit_price' => 'required|numeric|min:0'
        ]);

        try {
            // Get all receivings with this number
            $receivings = Receiving::where('receiving_number', $this->searchReceivingNumber)->get();

            if ($receivings->isEmpty()) {
                session()->flash('error', 'Receiving not found.');
                return;
            }

            // Begin transaction
            \DB::beginTransaction();

            // Update each receiving record
            foreach ($receivings as $receiving) {
                $receiving->update([
                    'received_at' => $this->date,
                    'department_id' => $this->selectedDepartment,
                    'supplier_id' => $this->selectedSupplier
                ]);
            }

            // Delete old receiving records that are not in the updated items
            $updatedItemIds = collect($this->receivingItems)->pluck('id');
            Receiving::where('receiving_number', $this->searchReceivingNumber)
                    ->whereNotIn('item_id', $updatedItemIds)
                    ->delete();

            // Update or create new receiving records for each item
            foreach ($this->receivingItems as $item) {
                Receiving::updateOrCreate(
                    [
                        'receiving_number' => $this->searchReceivingNumber,
                        'item_id' => $item['id']
                    ],
                    [
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'received_at' => $this->date,
                        'department_id' => $this->selectedDepartment,
                        'supplier_id' => $this->selectedSupplier
                    ]
                );
            }

            \DB::commit();
            session()->flash('message', 'Receiving updated successfully.');
            $this->resetForm();

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Error updating receiving: ' . $e->getMessage());
        }
    }
    public function resetForm()
    {
        $this->searchReceivingNumber = '';
        $this->receivingItems = [];
        $this->editMode = false;
        $this->date = now()->toDateString();
        $this->selectedDepartment = null;
        $this->selectedSupplier = null;
    }
    public function updatedItemSearch()
    {
        
        if (empty($this->itemSearch)) {
            $this->items = []; // Clear the items list if the search term is empty
        } else {
        $this->items = Item::where('name', 'like', '%' . $this->itemSearch . '%')
                        ->orWhere('code', 'like', '%' . $this->itemSearch . '%')
                        ->get()
                        ->toArray();
        }
    }
    public function selectFirstItem()
    {
        if (!empty($this->items)) {
            // Select the first item
            $firstItem = $this->items[0];
            
            // Check if the item exists before adding
            $item = Item::find($firstItem['id']);
            if ($item) {
                // Add the selected item to your selectedItems array
                $this->selectedItems[] = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => 1,
                    'unit_price' => 1,
                    'total' => $item->unit_price * $item->quantity
                ];
                $this->calculateTotals(); // Recalculate totals after adding the item
            }

            // Clear the items list and search input
            $this->items = [];
            $this->itemSearch = '';
        }
    }
    public function addItem($itemId)
    {
        $item = Item::find($itemId);
        if ($item) {
            $this->selectedItems[] = [
                'id' => $item->id,
                'name' => $item->name,
                'quantity' => 1,
                'unit_price' => 1,
                'unit_id' => 1,
                'total' => $item->unit_price * $item->quantity
            ];
            $this->calculateTotals();
            // Clear the items list and search input
            $this->items = [];
            $this->itemSearch = '';
        }
    }

    public function calculateTotal($index)
    {
        return $this->selectedItems[$index]['quantity'] * $this->selectedItems[$index]['unit_price'];
    }
    public function calculateTotals()

    {
        $this->subtotal = array_reduce($this->selectedItems, function($carry, $item) {
            return $carry + ($item['quantity'] * $item['unit_price']);
        }, 0);
    
        $this->tax = $this->applyTax ? ($this->subtotal * ($this->taxRate / 100)) : 0;
        $this->discount = $this->applyDiscount ? ($this->subtotal * ($this->discountRate / 100)) : 0;
        $this->total = $this->subtotal + $this->tax - $this->discount;
    }
    public function updatedSelectedItems($index)
    {
        $this->calculateTotals(); // Recalculate totals whenever selected items are updated
    }
    public function updatedSupplierSearch()
    {
        $this->suppliers = Supplier::where('name', 'like', '%' . $this->supplierSearch . '%')->get()->toArray();
    }

    public function updatedDepartmentSearch()
    {
        $this->departments = Department::where('name', 'like', '%' . $this->departmentSearch . '%')->get()->toArray();
    }
    public function updatedApplyTax()
    {
        $this->calculateTotals(); // Recalculate totals when tax checkbox is updated
    }
    
    public function updatedApplyDiscount()
    {
        $this->calculateTotals(); // Recalculate totals when discount checkbox is updated
    }
    public function selectSupplier($supplierId)
    {
        $this->selectedSupplier = Supplier::find($supplierId);
        $this->supplierSearch = $this->selectedSupplier->name; // Set input to selected supplier name
        $this->suppliers = []; // Clear search results
    }

    public function selectDepartment($departmentId)
    {
        $this->selectedDepartment = Department::find($departmentId);
        $this->departmentSearch = $this->selectedDepartment->name; // Set input to selected department name
        $this->departments = []; // Clear search results
    }
    public function removeItem($index)
    {
        // Remove the item from the selectedItems array
        unset($this->selectedItems[$index]);
        
        // Re-index the array to maintain sequential keys
        $this->selectedItems = array_values($this->selectedItems);
        
        // Recalculate totals after removing the item
        $this->calculateTotals();
    }
    protected function rules()
{
    return [
        'Receiving_num' => 'required|string|max:255',
        'supplierSearch' => 'required|string|max:255',
        'departmentSearch' => 'required|string|max:255',
        'date' => 'required|date',
        'taxRate' => 'nullable|numeric',
        'discountRate' => 'nullable|numeric',
    ];
}
    public function save()
    {
        if (empty($this->Receiving_num)) {
            session()->flash('error', 'Receiving number is required.');
            return;
        }
        // $this->validate(); // Validate the input data
        // dd($this->Receiving_num);
        foreach ($this->selectedItems as $selectedItem) {
            Receiving::create([
                'item_id' => $selectedItem['id'],
                'supplier_id' => $this->selectedSupplier->id, // Get the selected supplier's ID
                'department_id' => $this->selectedDepartment->id, // Get the selected department's ID
                'quantity' => $selectedItem['quantity'],
                'unit_price' => $selectedItem['unit_price'],
                // 'unit_id' => $selectedItem['unit_id'],
                'unit_id' => 1,
                'received_at' => $this->date,
                'receiving_number' => $this->Receiving_num, // Use the receiving number from the input
                'tax' => $this->applyTax ? $this->taxRate : 0, // Store the tax rate
                'discount' => $this->applyDiscount ? $this->discountRate : 0, // Store the discount rate
            ]);
        }
    
        session()->flash('message', 'Items received successfully.');
        $this->reset();
    }

    public function render()
    {
        return view('livewire.receiving-form')->layout('layouts.app'); // Correct path to the layout
    }
}