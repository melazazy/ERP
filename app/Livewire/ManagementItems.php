<?php

namespace App\Livewire;

use App\Exports\ItemsExport;  // Create an export class for items
use App\Models\Category;  // Import Category model
use App\Models\Department;  // Import Department model
use App\Models\Item;
use App\Models\Receiving;  // Import Receiving model
use App\Models\Requisition;  // Import Requisition model
use App\Models\Subcategory;  // Import Subcategory model
use App\Models\Trust;  // Import Trust model
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;  // Import the Excel facade

class ManagementItems extends Component
{
    public $items;

    public $newItem = [
        'id' => null,  // Initialize id as null
        'name' => '',
        'code' => '',
        'category' => '',
        'subcategory' => '',
        // 'department' => '',
    ];

    public $categoryFilter = '';
    public $subcategoryFilter = '';
    public $itemFilter = '';
    public $categories = [];
    public $subcategories = [];
    public $departments = [];
    protected $listeners = ['deleteItem'];
    public $selectedCategory;
public $filteredSubcategories = [];

    public function mount()
    {
        $this->refreshItems();

        $this->categories = Category::all();  // Assuming you have a Category model
        $this->subcategories = Subcategory::all();  // Assuming you have a Subcategory model
        // $this->departments = Department::all();  // Assuming you have a Department model
        $this->items = Item::with(['category', 'subcategory', 'department'])->get();
        $this->selectedCategory = null;
        $this->filteredSubcategories = [];
    }
    public function updatedSelectedCategory($value)
    {
        if ($value) {
            $this->filteredSubcategories = Subcategory::where('category_id', $value)->get();
            $this->newItem['category'] = $value;
        } else {
            $this->filteredSubcategories = [];
            $this->newItem['category'] = '';
            $this->newItem['subcategory'] = '';
        }
    }
    public function exportItems()
    {
        return Excel::download(new ItemsExport, 'items.csv');
    }

    public function addItem()
    {
        // dd($this->newItem);
        // dd($_POST);
        $this->newItem['subcategory_id'] = $this->newItem['subcategory'];  // Map the selected subcategory to subcategory_id
        $this->newItem['category_id'] = $this->newItem['category'];  // Map the selected category to category_id
        Item::create($this->newItem);
        $this->resetNewItem();
        $this->refreshItems();
    }

   
    public function updateItem()
    {
        $this->validate([
            'newItem.name' => 'required|string|max:255',
            'newItem.code' => 'required|string|max:255|unique:items,code,' . $this->newItem['id'],
            'newItem.category' => 'required|exists:categories,id',
            'newItem.subcategory' => 'required|exists:subcategories,id',
            // 'newItem.department' => 'required|exists:departments,id',
        ]);

        $item = Item::find($this->newItem['id']);
        $item->update([
            'name' => $this->newItem['name'],
            'code' => $this->newItem['code'],
            'category_id' => $this->newItem['category'],
            'subcategory_id' => $this->newItem['subcategory'],
            // 'department_id' => $this->newItem['department'],
        ]);

        $this->reset('newItem');
        $this->items = Item::with(['category', 'subcategory', 'department'])->get();
        session()->flash('message', 'Item updated successfully.');
    }
    public function edit($id)
    {
        $item = Item::with(['subcategory', 'subcategory.category'])->find($id);
        $this->newItem = [
            'id' => $item->id,  // Include the ID for updating
            'name' => $item->name,
            'code' => $item->code,
            'category' => $item->subcategory->category_id,
            'subcategory' => $item->subcategory_id,
            // 'department' => $item->department_id,
        ];
    }

    public function deleteItem($id)
    {
        Item::destroy($id);
        $this->refreshItems();
    }

    public function refreshItems()
    {
        $this->items = Item::with(['subcategory', 'department', 'subcategory.category'])
            ->when($this->categoryFilter, function ($query) {
                return $query->whereHas('subcategory.category', function ($q) {
                    $q->where('name', 'like', '%' . $this->categoryFilter . '%');
                });
            })
            ->when($this->subcategoryFilter, function ($query) {
                return $query->whereHas('subcategory', function ($q) {
                    $q->where('name', 'like', '%' . $this->subcategoryFilter . '%');
                });
            })
            ->when($this->itemFilter, function ($query) {
                $searchTerms = explode(' ', $this->itemFilter);
                
                return $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $q->where(function ($q) use ($term) {
                            $q->where('name', 'like', '%' . $term . '%')
                               ->orWhere('code', 'like', '%' . $term . '%');
                        });
                    }
                });
            })
            ->get();

        // Calculate possible amounts for each item
        foreach ($this->items as $item) {
            $item->possible_amount = $this->calculatePossibleAmount($item->id);  // Add possible amount
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

    public function resetNewItem()
    {
        $this->newItem = [
            'name' => '',
            'category' => '',
            'subcategory' => '',
            // 'department' => '',
        ];
    }

    public function updatedCategoryFilter()
    {
        $this->refreshItems();
    }

    public function updatedSubcategoryFilter()
    {
        $this->refreshItems();
    }

    public function updatedItemFilter()
    {
        $this->refreshItems();
    }

    public function render()
    {
        return view('livewire.management-items')->layout('layouts.app');
    }
}
