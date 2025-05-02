<?php

namespace App\Livewire;

use Illuminate\Support\Collection;

trait EntitySearchTrait
{
    /**
     * Default pagination limit
     * 
     * @var int
     */
    protected $defaultPerPage = 25;
    
    /**
     * Multi-term search on any Eloquent model
     *
     * @param string $searchTerm
     * @param string $modelClass
     * @param array $columns
     * @param int|null $limit
     * @return array
     */
    protected function searchEntities(string $searchTerm, string $modelClass, array $columns, int $limit = null): array
    {
        $limit = $limit ?? $this->getPerPage();
        
        if (empty($searchTerm)) {
            return [];
        }

        $searchTerms = array_filter(array_map('trim', explode(' ', $searchTerm)));
        $query = $modelClass::query();

        foreach ($searchTerms as $term) {
            $query->where(function($q) use ($columns, $term) {
                foreach ($columns as $col) {
                    $q->orWhere($col, 'like', '%' . $term . '%');
                }
            });
        }

        return $query->select(array_merge(['id'], $columns))
                    ->limit($limit)
                    ->get()
                    ->toArray();
    }

    /**
     * Get the per page limit
     *
     * @return int
     */
    protected function getEntityPerPage(): int
    {
        return $this->perPage ?? $this->defaultPerPage;
    }
}