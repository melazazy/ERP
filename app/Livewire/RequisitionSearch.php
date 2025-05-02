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

    public $editingItemId = null;
    public $editingItem = [];

    public $data = [
        'items' => [],
        'total_quantity' => 0
    ];

    public function mount()
    {
        $this->items = Item::with(['subcategory', 'department', 'subcategory.category'])->get();
        $this->units = Unit::all();
        $this->requisitions = Requisition::with(['item', 'unit'])->get();
        $this->departments = Department::all()->toArray();
        $this->date = now()->toDateString();
    }

    public function updatedItemSearch()
    {
        $this->items = $this->searchItems($this->itemSearch, 10);
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
            $this->requisitionItems = Requisition::with(['item', 'unit'])
                ->where('requisition_number', $this->searchRequisitionNumber)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item' => $item->item,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit,
                    ];
                })->toArray();
        } else {
            $this->requisitionItems = [];
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

    private function calculateTotals()
    {
        $this->totalQuantity = collect($this->requisitions)
            ->sum('total_quantity');
    }

    public function render()
    {
        return view('livewire.requisition-search')->layout('layouts.app');
    }
}