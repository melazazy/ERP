<?php

namespace App\Livewire;

use App\Models\Item;
use Illuminate\Support\Collection;

trait ItemSearchTrait
{
     /**
     * Default pagination limit
     * 
     * @var int
     */
    protected $defaultPerPage = 25;
    
    /**
     * Search for items by name or code
     *
     * @param string $searchTerm
     * @param int|null $limit
     * @return array
     */
    
    protected function searchItems(string $searchTerm, int $limit = null): array
    {
        $limit = $limit ?? $this->getPerPage();

        if (empty($searchTerm)) {
            return [];
        }

        // Split search terms into individual words
        $searchTerms = array_filter(array_map('trim', explode(' ', $searchTerm)));

        // Build a query that matches any of the search terms
        $query = Item::query()
            ->leftJoin('receivings', 'items.id', '=', 'receivings.item_id')
            ->leftJoin('requisitions', 'items.id', '=', 'requisitions.item_id')
            ->leftJoin('trusts', 'items.id', '=', 'trusts.item_id')
            ->select([
                'items.id',
                'items.name',
                'items.code',
                \DB::raw('COALESCE(SUM(receivings.quantity), 0) as total_received'),
                \DB::raw('COALESCE(SUM(requisitions.quantity), 0) as total_requisitioned'),
                \DB::raw('COALESCE(SUM(trusts.quantity), 0) as total_trusted'),
                \DB::raw('(COALESCE(SUM(receivings.quantity), 0) - (COALESCE(SUM(requisitions.quantity), 0) + COALESCE(SUM(trusts.quantity), 0))) as available_quantity')
            ])
            ->groupBy(['items.id', 'items.name', 'items.code']);

        foreach ($searchTerms as $term) {
            $query->where(function($q) use ($term) {
                $q->where('items.name', 'like', '%' . $term . '%')
                   ->orWhere('items.code', 'like', '%' . $term . '%');
            });
        }

        // Order by relevance - give higher scores to exact matches and partial matches
        $query->orderByRaw("(
            CASE 
                WHEN items.name = ? THEN 2 
                WHEN items.code = ? THEN 2 
                WHEN items.name LIKE ? THEN 1 
                WHEN items.code LIKE ? THEN 1 
                ELSE 0 
            END
        ) DESC", [
            $searchTerm,  // Exact name match
            $searchTerm,  // Exact code match
            '%' . $searchTerm . '%',  // Partial name match
            '%' . $searchTerm . '%'   // Partial code match
        ]);

        // Remove the having clause to include all items regardless of available quantity
        // This ensures items with zero or negative available quantity are still searchable
        return $query
            ->limit($limit)
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'code' => $item->code,
                    'available_quantity' => $item->available_quantity
                ];
            })
            ->toArray();
    }
      /**
     * Get item details by ID
     *
     * @param int $itemId
     * @return array|null
     */
    protected function getItemDetails(int $itemId): ?array
    {
        return Item::select(['id', 'name', 'code', 'unit_id'])
            ->where('id', $itemId)
            ->first()
            ->toArray();
    }

    /**
     * Get all items with pagination
     *
     * @param int|null $perPage
     * @return Collection
     */
    protected function getAllItems(int $perPage = null): Collection
    {
        return Item::select(['id', 'name', 'code', 'unit_id'])
            ->paginate($perPage ?? $this->getPerPage());
    }

    /**
     * Get the per page limit
     *
     * @return int
     */
    protected function getItemPerPage(): int
    {
        return $this->perPage ?? $this->defaultPerPage;
    }
}
