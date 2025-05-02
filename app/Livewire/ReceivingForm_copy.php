<?php

namespace App\Livewire;

// use Livewire\Component;
// use App\Models\Item; // Import the Item model
// use App\Models\Receiving; // Import the Receiving model

// class ReceivingForm extends Component
// {
//     public $companyName;
//     public $address;
//     public $email;
//     public $invoiceNumber;
//     public $date;
//     public $dueDate;
//     public $items = [];
//     public $subtotal = 0;
//     public $taxRate = 14; // Example tax rate
//     public $tax = 0;
//     public $grandTotal = 0;
//     public $itemSearch;


//     public function mount()
//     {
//         $this->date = now()->toDateString(); // Set default date to today
//     }

//     public function searchItems()
// {
//     // Assuming you have a model called Item
//     $this->items = Item::where('name', 'like', '%' . $this->itemSearch . '%')
//                         ->orWhere('code', 'like', '%' . $this->itemSearch . '%')
//                         ->get()
//                         ->toArray();
// }

//     public function addItem()
//     {
//         // Logic to add selected item to the form
//     }

//     public function calculateTotal()
//     {
//         $this->total = ($this->subtotal + $this->tax) - $this->discount;
//     }

//     public function render()
//     {
//         return view('livewire.receiving-form')->layout('layouts.app'); // Correct path to the layout
//     }
// }


use Livewire\Component;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Department;
use App\Models\Receiving;

class ReceivingForm extends Component
{
    public $companyName;
    public $date;
    public $searchTerm = '';
    public $selectedItems = [];
    public $items = [];
    public $subtotal = 0;
    public $tax = 0;
    public $discount = 0;
    public $total = 0;
    public $supplierSearch;
    public $departmentSearch;
    public $suppliers = [];
    public $departments = [];
    public $selectedSupplier;
    public $selectedDepartment;
    public $itemSearch;
    public $itemCode;
    public function mount()
    {
        $this->date = now()->toDateString(); // Set default date to today
    }

    // public function updatedSearchTerm($value)
    public function updatedItemSearch()

    {
        // $this->items = Item::where('name', 'like', '%' . $value . '%')
        //                    ->orWhere('code', 'like', '%' . $value . '%')
        //                    ->get();
        if (empty($this->itemSearch)) {
            $this->items = []; // Clear the items list if the search term is empty
        } else {
        $this->items = Item::where('name', 'like', '%' . $this->itemSearch . '%')
                        ->orWhere('code', 'like', '%' . $this->itemSearch . '%')
                        ->get()
                        ->toArray();
        // dd($this->items);
        }
    }

    public function addItem($itemId)
    {
        $item = Item::find($itemId);
        $this->selectedItems[] = [
            'id' => $item->id,
            'name' => $item->name,
            'quantity' => 1,
            'unit_price' => $item->unit_price,
            'total' => $item->unit_price
        ];
        $this->calculateTotals();
    }
    public function selectFirstItem()
    {
        if (!empty($this->items)) {
            // Select the first item
            $firstItem = $this->items[0];
            $item = Item::find($firstItem['id']);
            // Add the selected item to your selectedItems array or perform any necessary action
            $this->selectedItems[] = [
                'id' => $firstItem['id'],
                'quantity' => 1, // Default quantity
                'unit_price' => $firstItem['unit_price'], // Assuming you have this in the item
            ];
            $this->calculateTotals();
            // Clear the items list
            $this->items = [];
            
            // Optionally, clear the search input
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

        $this->total = $this->subtotal + $this->tax - $this->discount;
    }
public function updatedSupplierSearch()
{
    $this->suppliers = Supplier::where('name', 'like', '%' . $this->supplierSearch . '%')->get()->toArray();
}

public function updatedDepartmentSearch()
{
    $this->departments = Department::where('name', 'like', '%' . $this->departmentSearch . '%')->get()->toArray();
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
    public function save()
    {
        foreach ($this->selectedItems as $selectedItem) {
            Receiving::create([
                'item_id' => $selectedItem['id'],
                'supplier_id' => 1, // Replace with actual supplier ID
                'department_id' => 1, // Replace with actual department ID
                'quantity' => $selectedItem['quantity'],
                'unit_price' => $selectedItem['unit_price'],
                'received_at' => $this->date,
            ]);
        }

        session()->flash('message', 'Items received successfully.');
        $this->reset();
    }

    public function render()
    {
        // return view('livewire.receiving-form');
                return view('livewire.receiving-form')->layout('layouts.app'); // Correct path to the layout

    }
}