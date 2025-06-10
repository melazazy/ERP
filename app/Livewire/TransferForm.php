<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Department;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Requisition;
use Illuminate\Support\Facades\DB;

class TransferForm extends Component
{
    use ItemSearchTrait;

    public $fromDepartmentId = '';
    public $toDepartmentId = '';
    public $selectedItems = [];
    public $itemSearch = '';
    public $items = [];
    public $departments = [];
    public $units = [];
    
    public function mount()
    {
        $this->departments = Department::all()->toArray();
        $this->units = Unit::all()->toArray();
    }
    
public function updatedItemSearch()
{
    $this->items = $this->searchItems($this->itemSearch, $this->getItemPerPage());
}

    public function loadItems()
    {
        // $this->items = Item::query()
        //     ->when($this->itemSearch, function($query) {
        //         $query->where('name', 'like', '%' . $this->itemSearch . '%')
        //             ->orWhere('code', 'like', '%' . $this->itemSearch . '%');
        //     })
        //     ->get()
        //     ->toArray();
    }

    public function addItem($itemId)
    {
        $item = collect($this->items)->firstWhere('id', $itemId);
        if ($item && !collect($this->selectedItems)->contains('item_id', $itemId)) {
            $this->selectedItems[] = [
                'item_id' => $item['id'],
                'item_name' => $item['name'],
                'quantity' => null,
                'unit_id' => null
            ];
            $this->items = [];
            $this->itemSearch = '';
        }
    }

    public function removeItem($index)
    {
        unset($this->selectedItems[$index]);
        $this->selectedItems = array_values($this->selectedItems);
    }

    protected function generateTransferNumber()
    {
        $lastReq = Requisition::where('requisition_number', 'like', 'TRF%')
            ->orderBy('requisition_number', 'desc')
            ->first();

        if (!$lastReq) {
            return 'TRF00001';
        }

        $lastNumber = (int) substr($lastReq->requisition_number, 3);
        $newNumber = $lastNumber + 1;
        return 'TRF' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    public function checkAvailability($itemId, $quantity, $unitId)
    {
        if (!$quantity || !$unitId) {
            return false;
        }

        return Requisition::where('department_id', $this->fromDepartmentId)
            ->where('item_id', $itemId)
            ->where('unit_id', $unitId)
            ->where('quantity', '>=', $quantity)
            ->exists();
    }

    public function save()
    {
        $this->validate([
            'fromDepartmentId' => 'required|exists:departments,id',
            'toDepartmentId' => 'required|exists:departments,id|different:fromDepartmentId',
            'selectedItems' => 'required|array|min:1',
            'selectedItems.*.quantity' => 'required|numeric|min:0.0001',
            'selectedItems.*.unit_id' => 'required|exists:units,id'
        ], [
            'selectedItems.*.quantity.required' => 'Quantity is required',
            'selectedItems.*.quantity.numeric' => 'Quantity must be a number',
            'selectedItems.*.quantity.min' => 'Quantity must be greater than 0',
            'selectedItems.*.unit_id.required' => 'Unit is required',
            'selectedItems.*.unit_id.exists' => 'Please select a valid unit'
        ]);

        try {
            DB::beginTransaction();

            // Check availability for all items
            foreach ($this->selectedItems as $item) {
                if (!$this->checkAvailability($item['item_id'], $item['quantity'], $item['unit_id'])) {
                    throw new \Exception("Insufficient quantity available for item: {$item['item_name']} with selected unit");
                }
            }

            $transferNumber = $this->generateTransferNumber();

            foreach ($this->selectedItems as $item) {
                // Find and update source requisition
                $sourceReq = Requisition::where('department_id', $this->fromDepartmentId)
                    ->where('item_id', $item['item_id'])
                    ->where('unit_id', $item['unit_id'])
                    ->where('quantity', '>=', $item['quantity'])
                    ->first();

                if (!$sourceReq) {
                    throw new \Exception("Item no longer available: {$item['item_name']} with selected unit");
                }

                // Update source quantity
                $newQuantity = $sourceReq->quantity - $item['quantity'];
                if ($newQuantity <= 0) {
                    $sourceReq->delete();
                } else {
                    $sourceReq->update(['quantity' => $newQuantity]);
                }

                // Create new requisition for destination
                Requisition::create([
                    'requisition_number' => $transferNumber,
                    'item_id' => $item['item_id'],
                    'department_id' => $this->toDepartmentId,
                    'quantity' => $item['quantity'],
                    'requested_date' => now(),
                    'status' => 'approved',
                    'unit_id' => $item['unit_id']
                ]);
            }

            DB::commit();
            session()->flash('success', 'Items transferred successfully with transfer number: ' . $transferNumber);
            $this->redirect('/transfer');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.transfer-form')->layout('layouts.app');
    }
}