<?php

namespace App\Livewire;

use App\Livewire\ItemSearchTrait;
use Livewire\Component;
use App\Models\Trust;
use App\Models\Item;
use App\Models\Department;
use App\Models\User;

class TrustSearch extends Component
{
    use ItemSearchTrait;

    public $searchTrustNumber = '';
    public $trustItems = [];
    public $totalQuantity = 0;
    public $date = '';
    public $selectedDepartmentId;
    public $departments = [];
    public $items = [];
    public $itemSearch = '';
    public $searchedItems = [];
    public $selectedItemId = null;
    public $users = [];
    public $currentRequisitionNumber;

    public $editingItemId = null;
    public $editingItem = [];
    public $showDeleteConfirmation = false;
    public $showDeleteItemConfirmation = null;

    public function mount()
    {
        $this->items = Item::with(['subcategory', 'department', 'subcategory.category'])->get()->toArray();
        $this->departments = Department::all()->toArray();
        $this->users = User::all()->toArray();
        $this->date = now()->toDateString();
    }

    public function updatedItemSearch()
    {
        if (strlen($this->itemSearch) >= 2 && $this->currentRequisitionNumber) {
            $this->searchedItems = Trust::where('requisition_number', $this->currentRequisitionNumber)
                ->whereHas('item', function ($query) {
                    $query->where('code', 'like', '%' . $this->itemSearch . '%')
                        ->orWhere('name', 'like', '%' . $this->itemSearch . '%');
                })
                ->with(['item' => function ($query) {
                    $query->with(['subcategory', 'department', 'subcategory.category']);
                }])
                ->get()
                ->map(function ($trust) {
                    return $trust->item;
                })
                ->unique('id')
                ->take(10)
                ->toArray();
        } else {
            $this->searchedItems = [];
        }
    }

    public function searchTrust()
    {
        if ($this->searchTrustNumber) {
            $this->trustItems = Trust::where('requisition_number', $this->searchTrustNumber)
                ->with(['item', 'department', 'user'])
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item' => $item->item,
                        'quantity' => $item->quantity,
                        'department' => $item->department,
                        'requested_by' => $item->user,
                        'status' => $item->status,
                        'requested_date' => $item->requested_date,
                    ];
                })->toArray();

            if (!empty($this->trustItems)) {
                $this->currentRequisitionNumber = $this->searchTrustNumber;
                $this->date = $this->trustItems[0]['requested_date'] ? date('Y-m-d', strtotime($this->trustItems[0]['requested_date'])) : now()->toDateString();
                $this->selectedDepartmentId = $this->trustItems[0]['department']['id'] ?? null;
            }
        } else {
            $this->trustItems = [];
            $this->date = now()->toDateString();
            $this->selectedDepartmentId = null;
            $this->currentRequisitionNumber = null;
        }
    }

    public function selectItem($itemId)
    {
        $item = Trust::where('requisition_number', $this->currentRequisitionNumber)
            ->with(['item' => function ($query) {
                $query->with(['subcategory', 'department', 'subcategory.category']);
            }])
            ->whereHas('item', function ($query) use ($itemId) {
                $query->where('id', $itemId);
            })
            ->first();

        if ($item) {
            $this->selectedItemId = $itemId;
            $this->editingItem['item_name'] = $item->item->name;
            $this->editingItem['item_code'] = $item->item->code;
            $this->itemSearch = $item->item->name;
            $this->searchedItems = [];
        }
    }

    public function editItem($itemId)
    {
        $this->editingItemId = $itemId;
        $item = Trust::where('requisition_number', $this->currentRequisitionNumber)
            ->with(['item', 'department', 'user'])
            ->find($itemId);
        
        if ($item) {
            $this->editingItem = [
                'id' => $item->id,
                'item_id' => $item->item_id,
                'quantity' => $item->quantity,
                'department_id' => $item->department_id,
                'status' => $item->status,
                'item_name' => $item->item->name,
                'item_code' => $item->item->code,
                'requested_by_id' => $item->requested_by,
            ];

            $this->selectedItemId = $item->item_id;
            $this->itemSearch = $item->item->name;
        }
    }

    public function saveItemChanges()
    {
        if (!$this->editingItemId) {
            return;
        }

        $item = Trust::find($this->editingItemId);
        
        $item->update([
            'item_id' => $this->selectedItemId,
            'quantity' => $this->editingItem['quantity'],
            'department_id' => $this->editingItem['department_id'],
            'status' => $this->editingItem['status'],
            'requested_by' => $this->editingItem['requested_by_id'],
        ]);

        $this->editingItemId = null;
        $this->editingItem = [];
        $this->selectedItemId = null;

        $this->refreshItems();
        session()->flash('message', 'Item updated successfully.');
    }

    public function cancelEdit()
    {
        $this->editingItemId = null;
        $this->editingItem = [];
        $this->selectedItemId = null;
        $this->itemSearch = '';
    }

    public function refreshItems()
    {
        $this->trustItems = Trust::where('requisition_number', $this->currentRequisitionNumber)
            ->with(['item', 'department', 'user'])->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'item' => $item->item,
                    'quantity' => $item->quantity,
                    'department' => $item->department,
                    'requested_by' => $item->user,
                    'status' => $item->status,
                    'requested_date' => $item->requested_date,
                ];
            })->toArray();

        $this->totalQuantity = collect($this->trustItems)->sum('quantity');
    }

    public function updateDateAndDepartment()
    {
        if (!$this->currentRequisitionNumber) {
            session()->flash('error', 'No trust selected.');
            return;
        }

        Trust::where('requisition_number', $this->currentRequisitionNumber)
            ->update([
                'requested_date' => $this->date,
                'department_id' => $this->selectedDepartmentId
            ]);

        $this->refreshItems();
        session()->flash('message', 'Date and department updated successfully.');
    }

    public function addNewItem()
    {
        if (!$this->currentRequisitionNumber) {
            session()->flash('error', 'No trust selected.');
            return;
        }

        if (!$this->selectedItemId) {
            session()->flash('error', 'Please select an item to add.');
            return;
        }

        // Check if item already exists in the trust
        $existingItem = Trust::where('requisition_number', $this->currentRequisitionNumber)
            ->where('item_id', $this->selectedItemId)
            ->first();

        if ($existingItem) {
            session()->flash('error', 'This item already exists in the trust.');
            return;
        }

        // Get the first item to copy some data
        $firstItem = Trust::where('requisition_number', $this->currentRequisitionNumber)->first();

        if (!$firstItem) {
            session()->flash('error', 'Invalid trust.');
            return;
        }

        // Create new trust item
        Trust::create([
            'item_id' => $this->selectedItemId,
            'department_id' => $this->selectedDepartmentId,
            'quantity' => 1, // Default quantity, can be edited
            'requested_by' => $firstItem->requested_by,
            'requisition_number' => $this->currentRequisitionNumber,
            'status' => 'pending',
            'requested_date' => $this->date,
        ]);

        $this->selectedItemId = null;
        $this->itemSearch = '';
        $this->refreshItems();
        session()->flash('message', 'Item added to trust successfully.');
    }

    public function removeItem($itemId)
    {
        $trustItem = Trust::find($itemId);
        if ($trustItem) {
            $trustItem->delete();
            $this->refreshItems();
            $this->showDeleteItemConfirmation = null; // Reset the confirmation state
            session()->flash('message', 'Item removed successfully.');
        }else {
            session()->flash('error', 'Item not found.');
        }
    }

    public function removeAllItems()
    {
        if (!$this->currentRequisitionNumber) {
            session()->flash('error', 'No trust selected.');
            return;
        }

        Trust::where('requisition_number', $this->currentRequisitionNumber)->delete();
        $this->trustItems = [];
        $this->currentRequisitionNumber = null;
        $this->searchTrustNumber = '';
        $this->date = now()->toDateString();
        $this->selectedDepartmentId = null;
        
        session()->flash('message', 'All items have been removed from the trust.');
    }

    public function render()
    {
        return view('livewire.trust-search')->layout('layouts.app');
    }
}