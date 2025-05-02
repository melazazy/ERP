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
        $query = Item::query();

        foreach ($searchTerms as $term) {
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                   ->orWhere('code', 'like', '%' . $term . '%');
            });
        }

        return $query
            ->select(['id', 'name', 'code'])
            ->limit($limit)
            ->get()
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
