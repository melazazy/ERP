<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Requisition;
use App\Models\Item;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RequisitionForm extends Component
{
    public $requisitions;
    public $requisitionNumber;
    public $requestedBy;
    public $users = [];
    public $requestedBySearch;
    public $newRequisition = [
        'id' => null,
        'item_id' => null,
        'department_id' => null,
        'quantity' => 1,
        'requested_by' => 'ahmed',   
        'status' => 'pending',
    ];
    public $selectedItems = []; // Array to hold selected items
    public $itemSearch = '';
    public $departmentSearch = '';
    public $items = [];
    public $departments = [];
    public $statusFilter = '';
    public $itemFilter = '';
    public $departmentFilter = '';
    protected $listeners = ['deleteRequisition'];

    public function mount()
    {
        $this->refreshRequisitions();
        // $this->departments = Department::all();
    }

    public function addRequisition()
    {
        // Ensure requested_by is set
        if (empty($this->newRequisition['requested_by'])) {
            session()->flash('error', 'Requested by user is required.');
            return; // Prevent submission if requested_by is not set
        }
    
        // Ensure requisition_number is set
        if (empty($this->newRequisition['requisition_number'])) {
            session()->flash('error', 'Requisition number is required.');
            return; // Prevent submission if requisition_number is not set
        }
    
        // Loop through selected items and create requisitions
        foreach ($this->selectedItems as $item) {
            Requisition::create([
                'item_id' => $item['id'],
                'department_id' => $this->newRequisition['department_id'],
                'quantity' => $item['quantity'],
                'requested_by' => $this->newRequisition['requested_by'],
                'requisition_number' => $this->newRequisition['requisition_number'], // Include requisition number
                'status' => $this->newRequisition['status'],
            ]);
        }
    
        $this->resetNewRequisition();
        $this->refreshRequisitions();
        $this->selectedItems = []; // Clear selected items after saving
    }

    public function updateRequisition()
    {
        $requisition = Requisition::find($this->newRequisition['id']);
        if ($requisition) {
            $requisition->update([
                'item_id' => $this->newRequisition['item_id'] ?? $requisition->item_id,
                'department_id' => $this->newRequisition['department_id'] ?? $requisition->department_id,
                'quantity' => $this->newRequisition['quantity'] ?? $requisition->quantity,
                'status' => $this->newRequisition['status'] ?? $requisition->status,
            ]);
            $this->resetNewRequisition();
            $this->refreshRequisitions();
        } else {
            session()->flash('error', 'Requisition not found.');
        }
    }

    public function edit($id)
    {
        $requisition = Requisition::find($id);
        $this->newRequisition = [
            'id' => $requisition->id,
            'item_id' => $requisition->item_id,
            'department_id' => $requisition->department_id,
            'quantity' => $requisition->quantity,
            'status' => $requisition->status,
        ];
    }

    public function deleteRequisition($id)
    {
        Requisition::destroy($id);
        $this->refreshRequisitions();
    }

    public function refreshRequisitions()
    {
        $this->requisitions = Requisition::with(['item', 'department', 'requester'])
            ->when($this->itemFilter, function ($query) {
                return $query->whereHas('item', function ($q) {
                    $q->where('name', 'like', '%' . $this->itemFilter . '%');
                });
            })
            ->when($this->departmentFilter, function ($query) {
                return $query->whereHas('department', function ($q) {
                    $q->where('name', 'like', '%' . $this->departmentFilter . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                return $query->where('status', 'like', '%' . $this->statusFilter . '%');
            })
            ->get();
    }

    public function resetNewRequisition()
    {
        $this->newRequisition = [
            'department_id' => '',
            'status' => 'pending',
        ];
    }

    public function updatedRequestedBySearch($value)
{
    if (empty($value)) {
        $this->users = []; // Clear the list if the search is empty
    } else {
        $this->users = User::where('name', 'like', '%' . $value . '%')
            ->get()
            ->toArray();
    }
}

public function selectUser($userId)
{
    $this->newRequisition['requested_by'] = $userId; // Set the selected user ID
    $this->requestedBySearch = User::find($userId)->name; // Set input to selected user name
    $this->users = []; // Clear search results
}
    public function updatedItemSearch()
    {
        if (empty($this->itemSearch)) {
            $this->items = [];
        } else {
            $this->items = Item::where('name', 'like', '%' . $this->itemSearch . '%')
                ->orWhere('code', 'like', '%' . $this->itemSearch . '%')
                ->get()
                ->toArray();
        }
    }

    public function updatedDepartmentSearch($value)
{
    if (empty($value)) {
        $this->departments = []; // Clear the list if search is empty
    } else {
        $this->departments = Department::where('name', 'like', '%' . $value . '%')
            ->get()
            ->toArray();
    }
}

    public function selectItem($itemId)
    {
        $item = Item::find($itemId);
        if ($item) {
            // Add the item to the selected items list
            $this->selectedItems[] = [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'quantity' => 1, // Default quantity
            ];
            $this->itemSearch = ''; // Clear the search input
            $this->items = []; // Clear the search results
        }
    }

    public function selectDepartment($departmentId)
{
    $department = Department::find($departmentId);
    if ($department) {
        $this->newRequisition['department_id'] = $departmentId;
        $this->departmentSearch = $department->name;
        $this->departments = [];
    }
}


    public function removeSelectedItem($index)
    {
        if (isset($this->selectedItems[$index])) {
            unset($this->selectedItems[$index]);
            $this->selectedItems = array_values($this->selectedItems);
        }
    }

    public function updatedItemFilter()
    {
        $this->refreshRequisitions();
    }

    public function updatedDepartmentFilter()
    {
        $this->refreshRequisitions();
    }

    public function updatedStatusFilter()
    {
        $this->refreshRequisitions();
    }

    public function render()
    {
        return view('livewire.requisition-form')->layout('layouts.app');
    }
}