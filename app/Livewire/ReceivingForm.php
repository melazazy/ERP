<?php

namespace App\Livewire;

use App\Livewire\EntitySearchTrait;
use App\Livewire\FormResetTrait;
use App\Livewire\ItemSearchTrait;
use App\Livewire\QuantityCalculationTrait;
use App\Models\Department;
use App\Models\Item;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ReceivingForm extends Component
{
    use WithPagination;
    use ItemSearchTrait;
    use EntitySearchTrait;
    use FormResetTrait;
    use QuantityCalculationTrait;

    public $searchReceivingNumber;
    public $receivingItems = [];
    public $editMode = false;
    public $companyName;
    public $date;
    public $receiving_num = '000';  // Fixed variable name and spelling
    public $searchTerm = '';
    public $selectedItems = [];
    public $items = [];
    public $subtotal = 0;
    public $tax = 0;
    public $taxRate = 14;
    public $discount = 0;
    public $discountRate = 0;
    public $total = 0;
    public $supplierSearch = '';
    public $departmentSearch = '';
    public $suppliers = [];
    public $departments = [];
    public $selectedSupplierId = null;  // Changed to store ID instead of object
    public $selectedDepartmentId = null;  // Changed to store ID instead of object
    public $itemSearch = '';
    public $itemCode;
    public $applyTax = true;
    public $applyDiscount = false;
    public $createRequisition = false;
    public $units = [];
    public $perPage = 25;

    protected $resetProperties = [
        'searchReceivingNumber',
        'receivingItems',
        'editMode',
        'date',
        'selectedDepartmentId',
        'selectedSupplierId',
        'receiving_num',
        'selectedItems',
        'itemSearch',
        'supplierSearch',
        'departmentSearch',
        'items',
        'subtotal',
        'tax',
        'discount',
        'total'
    ];

    public function mount()
    {
        $this->date = now()->toDateString();
        $this->perPage = 25;  // Will use the trait's default if not set
        $this->departments = Department::all()->toArray();
        $this->suppliers = Supplier::all()->toArray();
        $this->units = Unit::all()->toArray();
    }

    public function searchReceiving()
    {
        $this->validate([
            'searchReceivingNumber' => 'required|string'
        ]);

        try {
            // Get all items with this receiving number
            $receivings = Receiving::where('receiving_number', $this->searchReceivingNumber)
                ->with(['item', 'department', 'supplier', 'unit'])
                ->get();

            if ($receivings->isEmpty()) {
                session()->flash('error', 'Receiving number not found.');
                return;
            }

            // Create array of items
            $this->receivingItems = $receivings->map(function ($receiving) {
                $item = $receiving->item;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'quantity' => $receiving->quantity,
                    'unit_price' => $receiving->unit_price,
                    'unit_id' => $receiving->unit_id,
                    'total' => $receiving->quantity * $receiving->unit_price
                ];
            })->toArray();

            // Get the first receiving to get common data
            $firstReceiving = $receivings->first();
            $this->date = $firstReceiving->received_at ?? $firstReceiving->created_at;
            $this->selectedDepartmentId = $firstReceiving->department_id;
            $this->selectedSupplierId = $firstReceiving->supplier_id;
            $this->receiving_num = $firstReceiving->receiving_number;
            $this->editMode = true;

            // Set tax and discount rates
            $this->taxRate = $firstReceiving->tax ?? 14;
            $this->discountRate = $firstReceiving->discount ?? 0;
            $this->applyTax = $this->taxRate > 0;
            $this->applyDiscount = $this->discountRate > 0;

            // Calculate totals
            $this->calculateTotals();

            session()->flash('message', 'Found ' . count($this->receivingItems) . ' items.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error processing receiving: ' . $e->getMessage());
        }
    }

    public function updateReceiving()
    {
        $this->validate([
            'date' => 'required|date',
            'selectedDepartmentId' => 'required|exists:departments,id',
            'selectedSupplierId' => 'required|exists:suppliers,id',
            'receivingItems.*.quantity' => 'required|numeric|min:0.01',
            'receivingItems.*.unit_price' => 'required|numeric|min:0',
            'receivingItems.*.unit_id' => 'required|exists:units,id'
        ]);

        try {
            DB::beginTransaction();

            // Update each receiving record
            foreach ($this->receivingItems as $index => $item) {
                Receiving::where('receiving_number', $this->searchReceivingNumber)
                    ->where('item_id', $item['id'])
                    ->update([
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'unit_id' => $item['unit_id'],
                        'received_at' => $this->date,
                        'department_id' => $this->selectedDepartmentId,
                        'supplier_id' => $this->selectedSupplierId,
                        'tax' => $this->applyTax ? $this->taxRate : 0,
                        'discount' => $this->applyDiscount ? $this->discountRate : 0
                    ]);
            }

            DB::commit();
            session()->flash('message', 'Receiving updated successfully.');
            $this->redirect('/receiving');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating receiving: ' . $e->getMessage());
        }
    }

    // public function resetForm()
    // {
    //     $this->searchReceivingNumber = '';
    //     $this->receivingItems = [];
    //     $this->editMode = false;
    //     $this->date = now()->toDateString();
    //     $this->selectedDepartmentId = null;
    //     $this->selectedSupplierId = null;
    //     $this->receiving_num = '000';
    //     $this->selectedItems = [];
    //     $this->itemSearch = '';
    //     $this->supplierSearch = '';
    //     $this->departmentSearch = '';
    //     $this->items = [];
    //     $this->subtotal = 0;
    //     $this->tax = 0;
    //     $this->discount = 0;
    //     $this->total = 0;
    // }


    public function resetForm()
    {
        parent::resetForm();
    }

    public function updatedItemSearch()
    {
        $this->items = $this->searchItems($this->itemSearch, $this->getItemPerPage());
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
                    'code' => $item->code,
                    'quantity' => 1,
                    'unit_price' => 0,  // Set default values
                    'unit_id' => $item->unit_id ?? $this->units[0]['id'] ?? 1,
                    'total' => 0  // Will be recalculated
                ];
                $this->calculateTotals();  // Recalculate totals after adding the item
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
                'code' => $item->code,
                'quantity' => 1,
                'unit_price' => 0,  // Set default values
                'unit_id' => $this->units[0]['id'] ?? 1,  // Default to first unit or 1
                'total' => 0  // Will be recalculated
            ];

            $this->calculateTotals();
            // Clear the items list and search input
            $this->items = [];
            $this->itemSearch = '';
        }
    }

    // This method is called when any item in selectedItems is updated
    public function updated($name)
    {
        // Auto-calculate on any relevant changes
        if (str_contains($name, 'receivingItems.') ||
                str_contains($name, 'selectedItems.') ||
                in_array($name, ['applyTax', 'applyDiscount', 'taxRate', 'discountRate'])) {
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $items = $this->editMode ? $this->receivingItems : $this->selectedItems;

        // Calculate subtotal
        $this->subtotal = collect($items)->sum(function ($item) {
            return $item['quantity'] * $item['unit_price'];
        });

        // Calculate tax and discount
        $this->tax = $this->applyTax ? ($this->subtotal * ($this->taxRate / 100)) : 0;
        $this->discount = $this->applyDiscount ? ($this->subtotal * ($this->discountRate / 100)) : 0;

        // Calculate total
        $this->total = $this->subtotal + $this->tax - $this->discount;

        // Update individual item totals
        if ($this->editMode) {
            foreach ($this->receivingItems as $index => $item) {
                $this->receivingItems[$index]['total'] = $item['quantity'] * $item['unit_price'];
            }
        } else {
            foreach ($this->selectedItems as $index => $item) {
                $this->selectedItems[$index]['total'] = $item['quantity'] * $item['unit_price'];
            }
        }
    }

    // public function updatedSupplierSearch()
    // {
    //     if (empty($this->supplierSearch)) {
    //         $this->suppliers = [];
    //     } else {
    //         $this->suppliers = Supplier::where('name', 'like', '%' . $this->supplierSearch . '%')
    //             ->limit(10)
    //             ->get()
    //             ->toArray();
    //     }
    // }

    public function updatedSupplierSearch()
    {
        $this->suppliers = $this->searchEntities(
            $this->supplierSearch,
            Supplier::class,
            ['name'],
            $this->getEntityPerPage()
        );
    }

    // public function updatedDepartmentSearch()
    // {
    //     if (empty($this->departmentSearch)) {
    //         $this->departments = [];
    //     } else {
    //         $this->departments = Department::where('name', 'like', '%' . $this->departmentSearch . '%')
    //             ->limit(10)
    //             ->get()
    //             ->toArray();
    //     }
    // }

    public function updatedDepartmentSearch()
    {
        $this->departments = $this->searchEntities(
            $this->departmentSearch,
            Department::class,
            ['name'],
            $this->getEntityPerPage()
        );
    }

    public function updatedApplyTax()
    {
        $this->calculateTotals();  // Recalculate totals when tax checkbox is updated
    }

    public function updatedApplyDiscount()
    {
        $this->calculateTotals();  // Recalculate totals when discount checkbox is updated
    }

    public function updatedTaxRate()
    {
        $this->calculateTotals();
    }

    public function updatedDiscountRate()
    {
        $this->calculateTotals();
    }

    public function selectSupplier($supplierId)
    {
        $supplier = Supplier::find($supplierId);
        if ($supplier) {
            $this->selectedSupplierId = $supplier->id;
            $this->supplierSearch = $supplier->name;  // Set input to selected supplier name
        }
        $this->suppliers = [];  // Clear search results
    }

    public function selectDepartment($departmentId)
    {
        $department = Department::find($departmentId);
        if ($department) {
            $this->selectedDepartmentId = $department->id;
            $this->departmentSearch = $department->name;  // Set input to selected department name
        }
        $this->departments = [];  // Clear search results
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
            'receiving_num' => 'required|string|max:255',
            'selectedSupplierId' => 'required|exists:suppliers,id',
            'selectedDepartmentId' => 'required|exists:departments,id',
            'date' => 'required|date',
            'taxRate' => 'nullable|numeric|min:0',
            'discountRate' => 'nullable|numeric|min:0',
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*.quantity' => 'required|numeric|min:0.01',
            'selectedItems.*.unit_price' => 'required|numeric|min:0',
            'selectedItems.*.unit_id' => 'required|exists:units,id',
        ];
    }

    protected function generateDirNumber()
    {
        $lastReq = Requisition::where('requisition_number', 'like', 'DIR%')
            ->orderBy('requisition_number', 'desc')
            ->first();

        if (!$lastReq) {
            return 'DIR00001';
        }

        $lastNumber = (int) substr($lastReq->requisition_number, 3);
        $newNumber = $lastNumber + 1;

        return 'DIR' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        // Run validation
        $this->validate();
        try {
            DB::beginTransaction();

            if (empty($this->receiving_num)) {
                session()->flash('error', 'Receiving number is required.');
                DB::rollBack();
                return;
            }

            if (empty($this->selectedItems)) {
                session()->flash('error', 'Please add at least one item.');
                DB::rollBack();
                return;
            }

            // Check if receiving number already exists
            $existingReceiving = Receiving::where('receiving_number', $this->receiving_num)->first();
            if ($existingReceiving) {
                session()->flash('error', 'Receiving number already exists.');
                DB::rollBack();
                return;
            }

            // Create receivings
            foreach ($this->selectedItems as $selectedItem) {
                Receiving::create([
                    'item_id' => $selectedItem['id'],
                    'supplier_id' => $this->selectedSupplierId,
                    'department_id' => $this->selectedDepartmentId,
                    'quantity' => $selectedItem['quantity'],
                    'unit_price' => $selectedItem['unit_price'],
                    'unit_id' => $selectedItem['unit_id'],
                    'received_at' => $this->date,
                    'receiving_number' => $this->receiving_num,
                    'tax' => $this->applyTax ? $this->taxRate : 0,
                    'discount' => $this->applyDiscount ? $this->discountRate : 0
                ]);
            }

            // Create automatic requisition if checkbox is checked
            if ($this->createRequisition) {
                $dirNumber = $this->generateDirNumber();

                foreach ($this->selectedItems as $selectedItem) {
                    Requisition::create([
                        'requisition_number' => $dirNumber,
                        'item_id' => $selectedItem['id'],
                        'department_id' => $this->selectedDepartmentId,
                        'quantity' => $selectedItem['quantity'],
                        'requested_date' => $this->date,
                        'status' => 'approved',  // Auto-approve DIR requisitions
                        'unit_id' => $selectedItem['unit_id']
                    ]);
                }
            }

            DB::commit();
            session()->flash('message', 'Items received successfully.' . ($this->createRequisition ? ' DIR Requisition created with number ' . $dirNumber : ''));
            $this->redirect('/receiving');
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error saving receiving: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.receiving-form')->layout('layouts.app');
    }
}
