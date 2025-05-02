<?php

namespace App\Livewire;

use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;

trait QuantityCalculationTrait
{
    /**
     * Calculate possible amount for an item
     *
     * @param int $itemId
     * @return float
     */
    public function calculatePossibleAmount(int $itemId): float
    {
        $totalReceiving = $this->getTotalReceiving($itemId);
        $totalRequisitions = $this->getTotalRequisitions($itemId);
        $totalTrusts = $this->getTotalTrusts($itemId);

        return max(0, $totalReceiving - ($totalRequisitions + $totalTrusts));
    }

    /**
     * Get total receiving quantity for an item
     *
     * @param int $itemId
     * @return float
     */
    protected function getTotalReceiving(int $itemId): float
    {
        return Receiving::where('item_id', $itemId)->sum('quantity');
    }

    /**
     * Get total requisition quantity for an item
     *
     * @param int $itemId
     * @return float
     */
    protected function getTotalRequisitions(int $itemId): float
    {
        return Requisition::where('item_id', $itemId)->sum('quantity');
    }

    /**
     * Get total trust quantity for an item
     *
     * @param int $itemId
     * @return float
     */
    protected function getTotalTrusts(int $itemId): float
    {
        return Trust::where('item_id', $itemId)->sum('quantity');
    }
}