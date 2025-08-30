<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Collection;
use Exception;

class InventoryService extends BaseService
{
    /**
     * Get all items with relationships and calculated quantities
     *
     * @param array $filters
     * @return Collection
     */
    public function getItemsWithQuantities(array $filters = []): Collection
    {
        $query = Item::with(['subcategory.category', 'department']);

        // Apply filters
        if (!empty($filters['category'])) {
            $query->whereHas('subcategory.category', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['category'] . '%');
            });
        }

        if (!empty($filters['subcategory'])) {
            $query->whereHas('subcategory', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['subcategory'] . '%');
            });
        }

        if (!empty($filters['search'])) {
            $searchTerms = explode(' ', $filters['search']);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->where(function ($sq) use ($term) {
                        $sq->where('name', 'like', '%' . $term . '%')
                           ->orWhere('code', 'like', '%' . $term . '%');
                    });
                }
            });
        }

        $items = $query->get();

        // Calculate quantities for each item
        foreach ($items as $item) {
            $item->possible_amount = $this->calculatePossibleAmount($item->id);
        }

        $this->logExecution(__METHOD__, $filters, ['count' => $items->count()]);

        return $items;
    }

    /**
     * Calculate possible amount for an item
     *
     * @param int $itemId
     * @return float
     */
    public function calculatePossibleAmount(int $itemId): float
    {
        $totalReceiving = Receiving::where('item_id', $itemId)->sum('quantity');
        $totalRequisitions = Requisition::where('item_id', $itemId)->sum('quantity');
        $totalTrusts = Trust::where('item_id', $itemId)->sum('quantity');

        return $totalReceiving - ($totalRequisitions + $totalTrusts);
    }

    /**
     * Create a new item
     *
     * @param array $itemData
     * @return Item
     * @throws Exception
     */
    public function createItem(array $itemData): Item
    {
        return $this->executeTransaction(function () use ($itemData) {
            $item = Item::create([
                'name' => $itemData['name'],
                'code' => $itemData['code'],
                'subcategory_id' => $itemData['subcategory_id'],
            ]);

            $this->logExecution(__METHOD__, $itemData, $item->id);
            return $item;
        }, 'Failed to create item');
    }

    /**
     * Update an existing item
     *
     * @param int $itemId
     * @param array $itemData
     * @return Item
     * @throws Exception
     */
    public function updateItem(int $itemId, array $itemData): Item
    {
        return $this->executeTransaction(function () use ($itemId, $itemData) {
            $item = Item::find($itemId);
            $this->validateModelExists($item, 'Item not found');

            $item->update([
                'name' => $itemData['name'],
                'code' => $itemData['code'],
                'subcategory_id' => $itemData['subcategory_id'],
            ]);

            $this->logExecution(__METHOD__, ['id' => $itemId, 'data' => $itemData], $item->id);
            return $item;
        }, 'Failed to update item');
    }

    /**
     * Delete an item
     *
     * @param int $itemId
     * @return bool
     * @throws Exception
     */
    public function deleteItem(int $itemId): bool
    {
        return $this->executeTransaction(function () use ($itemId) {
            $item = Item::find($itemId);
            $this->validateModelExists($item, 'Item not found');

            // Check if item has any transactions
            $hasTransactions = $item->receivings()->exists() || 
                             $item->requisitions()->exists() || 
                             $item->trusts()->exists();

            if ($hasTransactions) {
                throw new Exception('Cannot delete item with existing transactions');
            }

            $deleted = $item->delete();
            $this->logExecution(__METHOD__, ['id' => $itemId], $deleted);
            return $deleted;
        }, 'Failed to delete item');
    }

    /**
     * Get item movements for monitoring
     *
     * @param int $itemId
     * @return array
     * @throws Exception
     */
    public function getItemMovements(int $itemId): array
    {
        $item = Item::with([
            'receivings.supplier',
            'receivings.department',
            'requisitions.department',
            'trusts.department',
        ])->find($itemId);

        $this->validateModelExists($item, 'Item not found');

        $receivings = $item->receivings->map(function ($receiving) {
            return [
                'date' => $receiving->received_at,
                'document_number' => $receiving->receiving_number,
                'description' => ($receiving->supplier ? $receiving->supplier->name : '') . ' → ' . 
                               ($receiving->department ? $receiving->department->name : ''),
                'in' => $receiving->quantity,
                'out' => null,
                'balance' => null,
                'in_price' => $receiving->unit_price,
                'out_price' => null,
                'balance_price' => null,
                'type' => 'in',
                'transaction_type' => 'receiving'
            ];
        });

        $requisitions = $item->requisitions->map(function ($requisition) {
            return [
                'date' => $requisition->requested_date,
                'document_number' => $requisition->requisition_number,
                'description' => $requisition->department ? $requisition->department->name : '',
                'in' => null,
                'out' => $requisition->quantity,
                'balance' => null,
                'in_price' => null,
                'out_price' => null,
                'balance_price' => null,
                'type' => 'out',
                'transaction_type' => 'requisition'
            ];
        });

        $trusts = $item->trusts->map(function ($trust) {
            return [
                'date' => $trust->requested_date,
                'document_number' => $trust->trust_number ?? $trust->requisition_number,
                'description' => $trust->department ? $trust->department->name : '',
                'in' => null,
                'out' => $trust->quantity,
                'balance' => null,
                'in_price' => null,
                'out_price' => null,
                'balance_price' => null,
                'type' => 'out',
                'transaction_type' => 'trust'
            ];
        });

        $allMovements = $receivings
            ->concat($requisitions)
            ->concat($trusts)
            ->sortBy('date')
            ->values()
            ->toArray();

        // Calculate running balance and prices
        $balance = 0;
        $balancePrice = 0;
        $totalValue = 0;

        foreach ($allMovements as &$movement) {
            if ($movement['type'] === 'in') {
                $newQuantity = $movement['in'];
                $newPrice = $movement['in_price'];
                
                $totalValue = ($balance * $balancePrice) + ($newQuantity * $newPrice);
                $balance += $newQuantity;
                $balancePrice = $balance > 0 ? $totalValue / $balance : 0;
            } else {
                $outQuantity = $movement['out'];
                $movement['out_price'] = $balancePrice;
                $balance -= $outQuantity;
                $totalValue = $balance * $balancePrice;
            }
            
            $movement['balance'] = $balance;
            $movement['balance_price'] = $balancePrice;
        }

        $this->logExecution(__METHOD__, ['item_id' => $itemId], ['movements_count' => count($allMovements)]);

        return $allMovements;
    }

    /**
     * Get inventory summary statistics
     *
     * @return array
     */
    public function getInventorySummary(): array
    {
        $totalItems = Item::count();
        $totalCategories = Category::count();
        $totalSubcategories = Subcategory::count();

        $totalReceivings = Receiving::sum('quantity');
        $totalRequisitions = Requisition::sum('quantity');
        $totalTrusts = Trust::sum('quantity');
        $netQuantity = $totalReceivings - ($totalRequisitions + $totalTrusts);

        $summary = [
            'total_items' => $totalItems,
            'total_categories' => $totalCategories,
            'total_subcategories' => $totalSubcategories,
            'total_receivings' => $totalReceivings,
            'total_requisitions' => $totalRequisitions,
            'total_trusts' => $totalTrusts,
            'net_quantity' => $netQuantity,
        ];

        $this->logExecution(__METHOD__, [], $summary);

        return $summary;
    }

    /**
     * Search items with pagination
     *
     * @param string $searchTerm
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function searchItems(string $searchTerm, int $perPage = 25)
    {
        $query = Item::with(['subcategory.category', 'department']);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('code', 'like', '%' . $searchTerm . '%');
            });
        }

        $items = $query->orderBy('name')->paginate($perPage);

        $this->logExecution(__METHOD__, ['search_term' => $searchTerm, 'per_page' => $perPage], ['count' => $items->count()]);

        return $items;
    }
}
