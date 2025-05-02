<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Subcategory;

class InventoryReports extends Component
{
    use WithPagination;

    public $search = '';
    public $category = '';
    public $subcategory = '';
    public $totalQuantity = 0;

    public function render()
    {
        $query = Item::query()
            ->with('subcategory.category')
            ->withSum('receivings', 'quantity')
            ->withSum('requisitions', 'quantity')
            ->withSum('trusts', 'quantity')
            ->withCount(['receivings', 'requisitions', 'trusts']);

        // Calculate total quantities
        $totals = \DB::table('items')
            ->select(
                \DB::raw('(
                    COALESCE((SELECT SUM(quantity) FROM receivings WHERE receivings.item_id = items.id), 0) -
                    COALESCE((SELECT SUM(quantity) FROM requisitions WHERE requisitions.item_id = items.id), 0) -
                    COALESCE((SELECT SUM(quantity) FROM trusts WHERE trusts.item_id = items.id), 0)
                ) as net_quantity')
            )
            ->first();

        $this->totalQuantity = $totals->net_quantity ?? 0;
        
        $items = $query->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->when($this->category, function($query) {
                $query->whereHas('subcategory.category', function($q) {
                    $q->where('id', $this->category);
                });
            })
            ->when($this->subcategory, function($query) {
                $query->where('subcategory_id', $this->subcategory);
            })
            ->orderBy('name')
            ->paginate(10);

        // Calculate net quantity for each item
        foreach ($items as $item) {
            $item->net_quantity = ($item->receivings_sum_quantity ?? 0) - 
                                ($item->requisitions_sum_quantity ?? 0) - 
                                ($item->trusts_sum_quantity ?? 0);
        }

        return view('livewire.inventory-reports', [
            'items' => $items,
            'categories' => Category::all(),
            'subcategories' => Subcategory::all(),
            'totalQuantity' => $this->totalQuantity
        ])->layout('layouts.app');
    }
}