<?php

namespace App\Livewire;

use App\Livewire\ItemSearchTrait;
use Livewire\Component;
use App\Models\Trust;
use App\Models\Item;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
    
    // Add missing properties
    public $search = '';
    public $status = '';
    public $startDate = '';
    public $endDate = '';

    public $editingItemId = null;
    public $editingItem = [];
    public $showDeleteConfirmation = false;
    public $showDeleteItemConfirmation = null;

    protected function rules()
    {
        return [
            'selectedItemId' => 'required|exists:items,id',
            'selectedDepartmentId' => 'required|exists:departments,id',
            'date' => ['required', 'date', 'before_or_equal:'.now()->toDateString()],
            'editingItem.quantity' => 'required|numeric|min:0.0001',
            'editingItem.department_id' => 'required|exists:departments,id',
            'editingItem.requested_by_id' => 'required|exists:users,id',
            'editingItem.status' => 'required|in:pending,approved,rejected',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
        ];
    }

    protected $messages = [
        'selectedItemId.required' => 'Please select an item.',
        'selectedDepartmentId.required' => 'Please select a department.',
        'date.before_or_equal' => 'The date cannot be in the future.',
        'editingItem.quantity.min' => 'The quantity must be greater than 0.',
        'endDate.after_or_equal' => 'End date must be after or equal to start date.',
    ];

    public function mount()
    {
        try {
            $this->departments = Department::select('id', 'name')->get()->toArray();
            $this->users = User::select('id', 'name')->get()->toArray();
            $this->date = now()->toDateString();
            $this->startDate = now()->subDays(30)->toDateString(); // Last 30 days by default
            $this->endDate = now()->toDateString();
        } catch (\Exception $e) {
            Log::error('Error in TrustSearch mount: ' . $e->getMessage());
            session()->flash('error', 'Error loading initial data. Please try again.');
        }
    }

    private function resetState()
    {
        $this->trustItems = [];
        $this->currentRequisitionNumber = null;
        $this->searchTrustNumber = '';
        $this->date = now()->toDateString();
        $this->selectedDepartmentId = null;
        $this->editingItem = [];
        $this->editingItemId = null;
        $this->selectedItemId = null;
        $this->itemSearch = '';
        $this->searchedItems = [];
    }

    public function updatedItemSearch($value)
    {
        if (strlen($value) >= 2) {
            try {
                $baseItems = $this->searchItems($value, 10);
                
                if (!empty($baseItems)) {
                    $itemIds = collect($baseItems)->pluck('id');
                    
                    $this->searchedItems = Item::whereIn('id', $itemIds)
                        ->with(['subcategory', 'department', 'subcategory.category'])
                        ->get()
                        ->map(function($item) use ($baseItems) {
                            $baseItem = collect($baseItems)->firstWhere('id', $item->id);
                            return [
                                'id' => $item->id,
                                'name' => $item->name,
                                'code' => $item->code,
                                'available_quantity' => $baseItem['available_quantity'] ?? 0,
                                'subcategory' => $item->subcategory ? [
                                    'id' => $item->subcategory->id,
                                    'name' => $item->subcategory->name,
                                    'category' => $item->subcategory->category ? [
                                        'id' => $item->subcategory->category->id,
                                        'name' => $item->subcategory->category->name
                                    ] : null
                                ] : null,
                                'department' => $item->department ? [
                                    'id' => $item->department->id,
                                    'name' => $item->department->name
                                ] : null
                            ];
                        })
                        ->toArray();
                } else {
                    $this->searchedItems = [];
                }
            } catch (\Exception $e) {
                Log::error('Error in item search: ' . $e->getMessage());
                session()->flash('error', 'Error searching items. Please try again.');
                $this->searchedItems = [];
            }
        } else {
            $this->searchedItems = [];
        }
    }

    public function searchTrust($requisitionNumber = null)
    {
        if ($requisitionNumber) {
            $this->searchTrustNumber = $requisitionNumber;
        }

        if (empty($this->searchTrustNumber)) {
            session()->flash('error', 'Please enter a trust number.');
            return;
        }

        try {
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
            } else {
                session()->flash('info', 'No trust found with the given number.');
                $this->resetState();
            }
        } catch (\Exception $e) {
            Log::error('Error in searchTrust: ' . $e->getMessage());
            session()->flash('error', 'Error searching trust. Please try again.');
            $this->resetState();
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

    public function selectEditingItem($itemId)
    {
        $item = Item::with(['subcategory', 'department', 'subcategory.category'])
            ->find($itemId);

        if ($item) {
            $this->selectedItemId = $itemId;
            $this->editingItem['item_id'] = $itemId;
            $this->editingItem['item_name'] = $item->name;
            $this->editingItem['item_code'] = $item->code;
            $this->itemSearch = $item->name;
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
        try {
            if (!$this->editingItemId) {
                throw new \Exception('No item selected for editing.');
            }

            $this->validate();

            DB::transaction(function () {
                $item = Trust::findOrFail($this->editingItemId);
                
                $item->update([
                    'item_id' => $this->selectedItemId,
                    'quantity' => $this->editingItem['quantity'],
                    'department_id' => $this->editingItem['department_id'],
                    'status' => $this->editingItem['status'],
                    'requested_by' => $this->editingItem['requested_by_id'],
                ]);
            });

            $this->editingItemId = null;
            $this->editingItem = [];
            $this->selectedItemId = null;

            $this->refreshItems();
            session()->flash('message', 'Item updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error in saveItemChanges: ' . $e->getMessage());
            session()->flash('error', 'Error saving changes. Please try again.');
        }
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
        try {
            $this->trustItems = Trust::where('requisition_number', $this->currentRequisitionNumber)
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

            $this->totalQuantity = collect($this->trustItems)->sum('quantity');
        } catch (\Exception $e) {
            Log::error('Error in refreshItems: ' . $e->getMessage());
            session()->flash('error', 'Error refreshing items. Please try again.');
        }
    }

    public function updateDateAndDepartment()
    {
        if (!$this->currentRequisitionNumber) {
            session()->flash('error', 'No trust selected.');
            return;
        }

        try {
            $this->validate([
                'date' => 'required|date|before_or_equal:today',
                'selectedDepartmentId' => 'required|exists:departments,id',
            ]);

            DB::transaction(function () {
                Trust::where('requisition_number', $this->currentRequisitionNumber)
                    ->update([
                        'requested_date' => $this->date,
                        'department_id' => $this->selectedDepartmentId
                    ]);
            });

            $this->refreshItems();
            session()->flash('message', 'Date and department updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error in updateDateAndDepartment: ' . $e->getMessage());
            session()->flash('error', 'Error updating date and department. Please try again.');
        }
    }

    public function addNewItem()
    {
        try {
            $this->validate([
                'selectedItemId' => 'required|exists:items,id',
                'selectedDepartmentId' => 'required|exists:departments,id',
                'date' => 'required|date|before_or_equal:today',
            ]);

            if (!$this->currentRequisitionNumber) {
                session()->flash('error', 'No trust selected.');
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

            DB::transaction(function () {
                // Get the first item to copy some data
                $firstItem = Trust::where('requisition_number', $this->currentRequisitionNumber)->firstOrFail();

                // Create new trust item
                Trust::create([
                    'item_id' => $this->selectedItemId,
                    'department_id' => $this->selectedDepartmentId,
                    'quantity' => 1,
                    'requested_by' => $firstItem->requested_by,
                    'requisition_number' => $this->currentRequisitionNumber,
                    'status' => 'pending',
                    'requested_date' => $this->date,
                ]);
            });

            $this->selectedItemId = null;
            $this->itemSearch = '';
            $this->refreshItems();
            session()->flash('message', 'Item added to trust successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error in addNewItem: ' . $e->getMessage());
            session()->flash('error', 'Error adding item. Please try again.');
        }
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

        try {
            DB::transaction(function () {
                Trust::where('requisition_number', $this->currentRequisitionNumber)->delete();
            });
            
            $this->resetState();
            session()->flash('message', 'All items have been removed from the trust.');
        } catch (\Exception $e) {
            Log::error('Error in removeAllItems: ' . $e->getMessage());
            session()->flash('error', 'Error removing items. Please try again.');
        }
    }

    public function updateSearch()
    {
        try {
            // Clear single trust search state
            $this->searchTrustNumber = '';
            $this->currentRequisitionNumber = null;

            $this->validate([
                'startDate' => 'nullable|date',
                'endDate' => 'nullable|date|after_or_equal:startDate',
            ]);

            $query = Trust::query()
                ->with(['item', 'department', 'user']);

            // Apply date filters
            if ($this->startDate && $this->endDate) {
                $query->whereBetween('requested_date', [$this->startDate, $this->endDate]);
            }

            // Apply status filter
            if ($this->status) {
                $query->where('status', $this->status);
            }

            // Apply search filter
            if ($this->search) {
                $query->where(function ($q) {
                    $q->whereHas('item', function ($itemQuery) {
                        $itemQuery->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('code', 'like', '%' . $this->search . '%');
                    })
                    ->orWhere('requisition_number', 'like', '%' . $this->search . '%');
                });
            }

            // Get results and group by requisition_number
            $results = $query->get()
                ->groupBy('requisition_number')
                ->map(function ($items) {
                    $firstItem = $items->first();
                    return [
                        'requisition_number' => $firstItem->requisition_number,
                        'requested_date' => $firstItem->requested_date,
                        'department' => $firstItem->department,
                        'requested_by' => $firstItem->user,
                        'items' => $items->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'item' => $item->item,
                                'quantity' => $item->quantity,
                                'status' => $item->status,
                            ];
                        })->toArray(),
                        'total_items' => $items->count(),
                        'total_quantity' => $items->sum('quantity'),
                        'statuses' => $items->pluck('status')->unique()->values()->toArray()
                    ];
                })->values()->toArray();

            $this->trustItems = $results;

            if (empty($this->trustItems)) {
                session()->flash('info', 'No trusts found matching the criteria.');
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Error in updateSearch: ' . $e->getMessage());
            session()->flash('error', 'Error updating search results. Please try again.');
        }
    }

    public function searchTrustNumber($requisitionNumber)
    {
        $this->searchTrustNumber = $requisitionNumber;
        $this->searchTrust();
    }

    public function render()
    {
        return view('livewire.trust-search')->layout('layouts.app');
    }
}