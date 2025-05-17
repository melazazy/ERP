<?php

namespace App\Livewire;

use App\Livewire\ItemSearchTrait;
use Livewire\Component;
use App\Models\Requisition;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class RequisitionSearch extends Component
{
    use ItemSearchTrait;

    public $searchNumbers = '';
    public $requisitions = [];
    public $totalQuantity = 0;

    // Search requisition variables
    public $searchRequisitionNumber = '';
    public $requisitionItems = [];
    public $date;
    public $selectedDepartmentId;
    public $departments = [];
    public $units = [];
    public $items = [];
    public $itemSearch = '';
    public $searchedItems = [];

    public $editingItemId = null;
    public $editingItem = [];
    public $showDeleteConfirmation = false;
    public $showDeleteItemConfirmation = null;
    public $currentRequisitionNumber;
    public $selectedItemId = null;

    protected $rules = [
        'editingItem.quantity' => 'required|numeric|min:0',
        'editingItem.unit_id' => 'required|exists:units,id',
        'editingItem.item_id' => 'required|exists:items,id',
        'editingItem.department_id' => 'required|exists:departments,id',
        'date' => 'required|date',
    ];

    public $data = [
        'items' => [],
        'total_quantity' => 0
    ];

    public function mount()
    {
        $this->items = Item::with(['subcategory', 'department', 'subcategory.category'])->get()->toArray();
        $this->units = Unit::all()->toArray();
        $this->requisitions = Requisition::with(['item', 'unit'])->get();
        $this->departments = Department::all()->toArray();
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

    // Multiple requisition search
    public function search()
    {
        if (empty($this->searchNumbers)) {
            return;
        }

        $numbers = array_map('trim', explode(',', $this->searchNumbers));
        
        $this->requisitions = Requisition::whereIn('requisition_number', $numbers)
            ->with(['item', 'department', 'unit'])
            ->get()
            ->groupBy('requisition_number')
            ->map(function ($items) {
                $total = $items->sum('quantity');
                return [
                    'items' => $items,
                    'total_quantity' => $total,
                    'date' => $items->first()->requested_date,
                    'department' => $items->first()->department
                ];
            })
            ->toArray();

        $this->calculateTotals();
    }

    // Single requisition search
    public function searchRequisition()
    {
        if ($this->searchRequisitionNumber) {
            $this->requisitionItems = Requisition::where('requisition_number', $this->searchRequisitionNumber)
                ->with(['item', 'department', 'unit'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item' => $item->item,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                        'department' => $item->department,
                        'status' => $item->status,
                        'requested_date' => $item->requested_date,
                        'note' => $item->note,
                    ];
                })->toArray();

            if (!empty($this->requisitionItems)) {
                $this->currentRequisitionNumber = $this->searchRequisitionNumber;
                $this->date = $this->requisitionItems[0]['requested_date'] ? date('Y-m-d', strtotime($this->requisitionItems[0]['requested_date'])) : now()->toDateString();
                $this->selectedDepartmentId = $this->requisitionItems[0]['department']['id'] ?? null;
            }
        } else {
            $this->requisitionItems = [];
            $this->date = now()->toDateString();
            $this->selectedDepartmentId = null;
            $this->currentRequisitionNumber = null;
        }
    }

    public function updateRequisition()
    {
        $this->validate([
            'date' => 'required|date',
            'selectedDepartmentId' => 'required|exists:departments,id',
            'requisitionItems.*.quantity' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            foreach ($this->requisitionItems as $item) {
                Requisition::where('id', $item['id'])->update([
                    'quantity' => $item['quantity'],
                    'department_id' => $this->selectedDepartmentId,
                    'requested_date' => $this->date
                ]);
            }

            DB::commit();
            session()->flash('message', 'Requisition updated successfully!');
            $this->searchRequisition(); // Refresh the data
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error updating requisition: ' . $e->getMessage());
        }
    }

    public function editItem($itemId)
    {
        $this->editingItemId = $itemId;
        $item = Requisition::find($itemId);
        
        $this->editingItem = [
            'id' => $item->id,
            'item_id' => $item->item_id,
            'quantity' => $item->quantity,
            'unit_id' => $item->unit_id,
        ];
    }

    public function saveItemChanges()
    {
        if (!$this->editingItemId) {
            return;
        }

        $item = Requisition::find($this->editingItemId);
        
        $item->update([
            'item_id' => $this->editingItem['item_id'],
            'quantity' => $this->editingItem['quantity'],
            'unit_id' => $this->editingItem['unit_id'],
        ]);

        $this->editingItemId = null;
        $this->editingItem = [];

        $this->refreshItems();
        session()->flash('message', 'Item updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingItemId = null;
        $this->editingItem = [];
    }

    public function selectEditingItem($itemId)
    {
        $this->editingItem['item_id'] = $itemId;
        $this->itemSearch = ''; // Clear the search after selection
        $this->searchedItems = []; // Clear search results
    }

    public function refreshItems()
    {
        $this->data['items'] = Requisition::with(['item', 'unit'])->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'item' => $item->item,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
            ];
        })->toArray();

        $this->data['total_quantity'] = collect($this->data['items'])->sum('quantity');
    }

    public function updateDateAndDepartment()
    {
        if (!$this->currentRequisitionNumber) {
            session()->flash('error', 'No requisition selected.');
            return;
        }

        $validated = $this->validate([
            'date' => 'required|date',
            'selectedDepartmentId' => 'required|exists:departments,id',
        ]);

        Requisition::where('requisition_number', $this->currentRequisitionNumber)
            ->update([
                'requested_date' => $validated['date'],
                'department_id' => $validated['selectedDepartmentId']
            ]);


        $this->searchRequisition();
        session()->flash('message', 'Date and department updated successfully.');
    }

    public function addNewItem()
    {
        if (!$this->currentRequisitionNumber) {
            session()->flash('error', 'No requisition selected.');
            return;
        }

        if (!$this->selectedItemId) {
            session()->flash('error', 'Please select an item to add.');
            return;
        }

        // Check if item already exists in the requisition
        $existingItem = Requisition::where('requisition_number', $this->currentRequisitionNumber)
            ->where('item_id', $this->selectedItemId)
            ->first();

        if ($existingItem) {
            session()->flash('error', 'This item already exists in the requisition.');
            return;
        }

        // Get the first item to copy some data
        $firstItem = Requisition::where('requisition_number', $this->currentRequisitionNumber)->first();

        if (!$firstItem) {
            session()->flash('error', 'Invalid requisition.');
            return;
        }

        // Create new requisition item
        Requisition::create([
            'item_id' => $this->selectedItemId,
            'department_id' => $this->selectedDepartmentId,
            'quantity' => 1, // Default quantity, can be edited
            'unit_id' => 1, // Default unit, should be set based on item
            'requested_date' => $this->date,
            'requisition_number' => $this->currentRequisitionNumber,
            'status' => 'pending',
            'note' => 'Added via update',
        ]);

        $this->selectedItemId = null;
        $this->itemSearch = '';
        $this->searchRequisition();
        session()->flash('message', 'Item added to requisition successfully.');
    }

    public function removeItem($itemId)
    {
        $requisitionItem = Requisition::find($itemId);
        if ($requisitionItem) {
            $requisitionItem->delete();
            $this->searchRequisition();
            $this->showDeleteItemConfirmation = null;
            session()->flash('message', 'Item removed successfully.');
        } else {
            session()->flash('error', 'Item not found.');
        }
    }

    public function removeAllItems()
    {
        if (!$this->currentRequisitionNumber) {
            session()->flash('error', 'No requisition selected.');
            return;
        }

        Requisition::where('requisition_number', $this->currentRequisitionNumber)->delete();
        $this->requisitionItems = [];
        $this->currentRequisitionNumber = null;
        $this->searchRequisitionNumber = '';
        $this->date = now()->toDateString();
        $this->selectedDepartmentId = null;
        
        session()->flash('message', 'All items have been removed from the requisition.');
    }

    private function calculateTotals()
    {
        $this->totalQuantity = collect($this->requisitions)
            ->sum('total_quantity');
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
        return view('livewire.requisition-search')->layout('layouts.app');
    }
}