<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Item;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RequisitionForm extends Component
{
    use ItemSearchTrait;

    public $requisitions;
    public $requisitionNumber;  // Add requisition number
    public $requestedBySearch;  // Add requested by search
    public $users = [];
    public $date;

    public $newRequisition = [
        'id' => null,
        'item_id' => null,
        'department_id' => null,
        'quantity' => 1,
        'requested_by' => null,  // Remove default value
        'requisition_number' => null,
        'requested_date' => null,
        'status' => 'pending',
    ];

    public $selectedItems = [];
    public $itemSearch = '';
    public $departmentSearch = '';
    public $items = [];
    public $departments = [];
    public $statusFilter = '';
    public $itemFilter = '';
    public $departmentFilter = '';
    protected $listeners = ['deleteRequisition'];
    // public $perPage = 25;

    public function mount()
    {
        $this->refreshRequisitions();
        $this->date = now()->toDateString();  // Set default date to today
        // $this->departments = Department::all();
    }

    protected function rules()
    {
        return [
            'newRequisition.department_id' => 'required|exists:departments,id',
            'newRequisition.requested_by' => 'nullable',
            'newRequisition.status' => 'required|in:pending,approved,rejected',
            'newRequisition.requested_date' => 'nullable|date',  // Updated rule
            'selectedItems.*.quantity' => 'required|numeric|min:0',  // Changed to accept float
            // ... other rules
        ];
    }
    public function getPerPage()
    {
        return 25; // Return your desired number of items per page
    }
    public function addRequisition()
    {


        if (empty($this->requisitionNumber)) {
            session()->flash('error', 'Requisition number is required.');
            return;
        }

        // Replace the existing duplicate check with this:
        if (Requisition::where('requisition_number', $this->requisitionNumber)->exists()) {
            // Extract base pattern (remove any trailing letters)
            $basePattern = preg_replace('/[A-Za-z]+$/', '', $this->requisitionNumber);

            // Find all existing suffixes for this base pattern
            $existingSuffixes = Requisition::where('requisition_number', 'like', $basePattern . '%')
                ->get()
                ->map(function ($req) use ($basePattern) {
                    return str_replace($basePattern, '', $req->requisition_number);
                })
                ->filter()
                ->sort()
                ->values()
                ->toArray();

            // Determine next available suffix starting from A
            $nextSuffix = 'A';
            $suffixChar = 'A';

            while (in_array($nextSuffix, $existingSuffixes)) {
                $suffixChar++;
                $nextSuffix = $suffixChar;

                // Safety check to prevent infinite loop
                if (strlen($nextSuffix) > 1) {
                    $nextSuffix = 'A';
                    break;
                }
            }

            $suggestion = $basePattern . $nextSuffix;
            session()->flash('error', "Requisition number already exists. Try using: $suggestion");
            return;
        }
        // Validate the form
        $validated = $this->validate();

        // Get the current date if requested_date is not provided
        $requestedDate = $validated['newRequisition']['requested_date'] ?? now()->toDateString();

        // First, validate all items
        $errors = [];
        foreach ($this->selectedItems as $item) {
            $possibleAmount = $this->calculatePossibleAmount($item['id']);

            if ($item['quantity'] > $possibleAmount) {
                $errors[] = 'Exceeded possible amount for item: ' . $item['name'] .
                    ' (Available: ' . $possibleAmount . ')';
            }
        }

        // If any errors, show them and return
        if (!empty($errors)) {
            session()->flash('error', implode('<br>', $errors));
            return;
        }
        // Loop through selected items and create requisitions
        foreach ($this->selectedItems as $item) {
            Requisition::create([
                'item_id' => $item['id'],
                'requested_date' => $this->date,  // Use the validated date
                'department_id' => $validated['newRequisition']['department_id'],
                'quantity' => $item['quantity'],
                'requested_by' => $validated['newRequisition']['requested_by'],
                'requisition_number' => $this->requisitionNumber,
                'status' => $validated['newRequisition']['status'],
            ]);
        }
        // Reset fields after saving
        $this->resetNewRequisition();
        // $this->refreshRequisitions();
        $this->selectedItems = [];
        $this->requisitionNumber = '';  // Reset requisition number
        $this->requestedBySearch = '';  // Reset requested by search
        session()->flash('success', 'Requisition saved successfully.');
        // $this->dispatchBrowserEvent('requisition-added');
        return redirect()->route('requisition'); // Refresh the page
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
            $this->users = [];
        } else {
            $this->users = User::where('name', 'like', '%' . $value . '%')
                ->get()
                ->toArray();
        }
    }

    public function selectUser($userId)
    {
        $this->newRequisition['requested_by'] = $userId;  // Set the selected user ID
        $this->requestedBySearch = User::find($userId)->name;  // Set input to selected user name
        $this->users = [];  // Clear search results
    }

    public function updatedItemSearch()
    {
        $this->items = $this->searchItems($this->itemSearch);
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
                    'unit_id' => $item->unit_id ?? $this->units[0]['id'] ?? 1,
                    'possible_amount' => $this->calculatePossibleAmount($item->id),
                ];
            }

            // Clear the items list and search input
            $this->items = [];
            $this->itemSearch = '';
        }
    }

    public function updatedDepartmentSearch($value)
    {
        if (empty($value)) {
            $this->departments = [];  // Clear the list if search is empty
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
                'quantity' => 1,  // Default quantity
                'unit_id' => $item->unit_id ?? $this->units[0]['id'] ?? 1,
                'possible_amount' => $this->calculatePossibleAmount($item->id),  // Calculate possible amount
            ];
            $this->itemSearch = '';  // Clear the search input
            $this->items = [];  // Clear the search results
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
