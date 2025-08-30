<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Item;
use App\Models\Trust;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use App\Livewire\QuantityCalculationTrait;

class TrustForm extends Component
{
    use ItemSearchTrait;
    use QuantityCalculationTrait;

    public $trusts;
    public $requisitionNumber;  // Add requisition number
    public $requestedBySearch;  // Add requested by search
    public $users = [];
    public $date;  // Add date property

    public $newTrust = [
        'id' => null,
        'item_id' => null,
        'department_id' => null,
        'quantity' => 1,
        'requested_by' => null,
        'requisition_number' => null,
        'status' => 'pending',
    ];

    public $selectedItems = [];
    public $itemSearch = '';
    public $departmentSearch = '';
    public $items = [];
    public $departments = [];
    protected $listeners = ['deleteTrust'];

    public function mount()
    {
        $this->date = now()->toDateString();
        $this->refreshTrusts();
        $this->searchAllItems();
    }

    public function searchAllItems()
    {
        $this->items = $this->searchItems('', 100); // Show first 100 items when empty search
    }

    public function addTrust()
    {
        // Validate required fields
        if (empty($this->newTrust['requested_by'])) {
            session()->flash('error', 'Requested by user is required.');
            return;
        }

        if (empty($this->requisitionNumber)) {
            session()->flash('error', 'Requisition number is required.');
            return;
        }

        // Loop through selected items and create trusts
        foreach ($this->selectedItems as $item) {
            $possibleAmount = $this->calculatePossibleAmount($item['id']); // Calculate possible amount

            // Check if quantity exceeds possible amount
            if ($item['quantity'] > $possibleAmount) {
                session()->flash('error', 'Exceeded possible amount for item: ' . $item['name']);
                return;
            }

            Trust::create([
                'item_id' => $item['id'],
                'department_id' => $this->newTrust['department_id'],
                'quantity' => $item['quantity'],
                'requested_by' => $this->newTrust['requested_by'],
                'requisition_number' => $this->requisitionNumber,
                'status' => $this->newTrust['status'],
                'requested_date' => $this->date,
            ]);
        }

        // Reset fields after saving
        $this->resetNewTrust();
        $this->refreshTrusts();
        $this->selectedItems = [];
        $this->requisitionNumber = '';  // Reset requisition number
        $this->requestedBySearch = '';  // Reset requested by search
        session()->flash('success', 'Trust saved successfully.');
    }

    public function deleteTrust($id)
    {
        Trust::destroy($id);
        $this->refreshTrusts();
    }

    public function refreshTrusts()
    {
        $this->trusts = Trust::with(['item', 'department', 'requester'])->get();
    }

    public function resetNewTrust()
    {
        $this->newTrust = [
            'department_id' => '',
            'status' => 'pending',
        ];
        $this->date = now()->toDateString();
    }

    public function updatedRequestedBySearch($value)
    {
        if (empty($value)) {
            $this->users = [];
        } else {
            $this->users = User::where('name', 'like', '%' . $value . '%')->get()->toArray();
        }
    }

    public function selectUser($userId)
    {
        $this->newTrust['requested_by'] = $userId;  // Set the selected user ID
        $this->requestedBySearch = User::find($userId)->name;  // Set input to selected user name
        $this->users = [];  // Clear search results
    }

    public function updatedItemSearch($value)
    {
        if (empty($value)) {
            $this->searchAllItems();
        } else {
            $this->items = $this->searchItems($value, 10);
        }
    }

    public function updatedDepartmentSearch($value)
    {
        if (empty($value)) {
            $this->departments = [];  // Clear the list if search is empty
        } else {
            $this->departments = Department::where('name', 'like', '%' . $value . '%')->get()->toArray();
        }
    }

    public function selectItem($itemId)
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        if ($item) {
            // Check if the item is already in the selected items
            if (collect($this->selectedItems)->contains('id', $itemId)) {
                session()->flash('error', __('messages.item_already_added'));
                return;
            }
            
            // Calculate available quantity using the same method as calculatePossibleAmount
            $availableQuantity = $this->calculatePossibleAmount($itemId);
            
            // Check if item is out of stock
            if ($availableQuantity <= 0) {
                session()->flash('error', __('messages.item_out_of_stock'));
                return;
            }
            
            // Add the item to the selected items list
            $this->selectedItems[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'code' => $item['code'],
                'quantity' => 1,  // Default quantity
                'available_quantity' => $availableQuantity,
            ];
            $this->itemSearch = '';  // Clear the search input
            $this->searchAllItems();  // Reset the items list
        }
    }

    public function calculatePossibleAmount($itemId)
    {
        // Fetch total receiving for the item
        $totalReceiving = Receiving::where('item_id', $itemId)->sum('quantity');

        // Fetch total requisitions for the item
        $totalRequisitions = Requisition::where('item_id', $itemId)->sum('quantity');

        // Fetch total trusts for the item
        $totalTrusts = Trust::where('item_id', $itemId)->sum('quantity');

        // Calculate possible amount
        return $totalReceiving - ($totalRequisitions + $totalTrusts);
    }

    public function selectDepartment($departmentId)
    {
        $department = Department::find($departmentId);
        if ($department) {
            $this->newTrust['department_id'] = $departmentId;
            $this->departmentSearch = $department->name;
            $this->departments = [];
        }
    }

    public function removeSelectedItem($index)
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems);  // Re-index the array
    }

    public function render()
    {
        return view('livewire.trust-form')->layout('layouts.app');
    }
}