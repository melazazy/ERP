<?php

namespace App\Services;

use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Department;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Exception;

class ReceivingService extends BaseService
{
    /**
     * Create receiving records for multiple items
     *
     * @param array $receivingData
     * @param array $selectedItems
     * @return array
     * @throws Exception
     */
    public function createReceiving(array $receivingData, array $selectedItems): array
    {
        return $this->executeTransaction(function () use ($receivingData, $selectedItems) {
            $receivingNumber = $receivingData['receiving_number'];
            
            // Check if receiving number already exists
            if (Receiving::where('receiving_number', $receivingNumber)->exists()) {
                throw new Exception('Receiving number already exists.');
            }

            $createdReceivings = [];
            $dirNumber = null;

            // Create receiving records
            foreach ($selectedItems as $selectedItem) {
                $receiving = Receiving::create([
                    'item_id' => $selectedItem['id'],
                    'supplier_id' => $receivingData['supplier_id'],
                    'department_id' => $receivingData['department_id'],
                    'quantity' => $selectedItem['quantity'],
                    'unit_price' => $selectedItem['unit_price'],
                    'unit_id' => $selectedItem['unit_id'],
                    'received_at' => $receivingData['date'],
                    'receiving_number' => $receivingNumber,
                    'tax' => $receivingData['apply_tax'] ? ($receivingData['tax_rate'] ?? 14) : 0,
                    'discount' => $receivingData['apply_discount'] ? ($receivingData['discount_rate'] ?? 0) : 0
                ]);

                $createdReceivings[] = $receiving;
            }

            // Create automatic requisition if requested
            if (!empty($receivingData['create_requisition'])) {
                $dirNumber = $this->generateDirNumber();
                
                foreach ($selectedItems as $selectedItem) {
                    Requisition::create([
                        'requisition_number' => $dirNumber,
                        'item_id' => $selectedItem['id'],
                        'department_id' => $receivingData['department_id'],
                        'quantity' => $selectedItem['quantity'],
                        'requested_date' => $receivingData['date'],
                        'status' => 'approved',
                        'unit_id' => $selectedItem['unit_id']
                    ]);
                }
            }

            $this->logExecution(__METHOD__, [
                'receiving_data' => $receivingData,
                'items_count' => count($selectedItems),
                'dir_number' => $dirNumber
            ], ['receiving_ids' => collect($createdReceivings)->pluck('id')]);

            return [
                'receivings' => $createdReceivings,
                'dir_number' => $dirNumber
            ];
        }, 'Failed to create receiving');
    }

    /**
     * Update existing receiving records
     *
     * @param string $receivingNumber
     * @param array $receivingData
     * @param array $receivingItems
     * @return array
     * @throws Exception
     */
    public function updateReceiving(string $receivingNumber, array $receivingData, array $receivingItems): array
    {
        return $this->executeTransaction(function () use ($receivingNumber, $receivingData, $receivingItems) {
            $updatedReceivings = [];

            // Update each receiving record
            foreach ($receivingItems as $item) {
                $receiving = Receiving::where('receiving_number', $receivingNumber)
                    ->where('item_id', $item['id'])
                    ->first();

                if (!$receiving) {
                    throw new Exception("Receiving record not found for item: {$item['name']}");
                }

                $receiving->update([
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'unit_id' => $item['unit_id'],
                    'received_at' => $receivingData['date'],
                    'department_id' => $receivingData['department_id'],
                    'supplier_id' => $receivingData['supplier_id'],
                    'tax' => $receivingData['apply_tax'] ? ($receivingData['tax_rate'] ?? 14) : 0,
                    'discount' => $receivingData['apply_discount'] ? ($receivingData['discount_rate'] ?? 0) : 0
                ]);

                $updatedReceivings[] = $receiving;
            }

            $this->logExecution(__METHOD__, [
                'receiving_number' => $receivingNumber,
                'receiving_data' => $receivingData,
                'items_count' => count($receivingItems)
            ], ['updated_ids' => collect($updatedReceivings)->pluck('id')]);

            return $updatedReceivings;
        }, 'Failed to update receiving');
    }

    /**
     * Search receiving by number
     *
     * @param string $receivingNumber
     * @return array
     * @throws Exception
     */
    public function searchReceiving(string $receivingNumber): array
    {
        $receivings = Receiving::where('receiving_number', $receivingNumber)
            ->with(['item', 'department', 'supplier', 'unit'])
            ->get();

        if ($receivings->isEmpty()) {
            throw new Exception('Receiving number not found.');
        }

        $firstReceiving = $receivings->first();
        
        $receivingItems = $receivings->map(function ($receiving) {
            $item = $receiving->item;
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'quantity' => $receiving->quantity,
                'unit_price' => $receiving->unit_price,
                'unit_id' => $receiving->unit_id,
                'total' => $receiving->quantity * $receiving->unit_price
            ];
        })->toArray();

        $commonData = [
            'date' => $firstReceiving->received_at ?? $firstReceiving->created_at,
            'department_id' => $firstReceiving->department_id,
            'supplier_id' => $firstReceiving->supplier_id,
            'receiving_number' => $firstReceiving->receiving_number,
            'tax_rate' => $firstReceiving->tax ?? 14,
            'discount_rate' => $firstReceiving->discount ?? 0,
            'apply_tax' => $firstReceiving->tax > 0,
            'apply_discount' => $firstReceiving->discount > 0
        ];

        $this->logExecution(__METHOD__, ['receiving_number' => $receivingNumber], [
            'items_count' => count($receivingItems),
            'common_data' => $commonData
        ]);

        return [
            'receiving_items' => $receivingItems,
            'common_data' => $commonData
        ];
    }

    /**
     * Calculate totals for receiving items
     *
     * @param array $items
     * @param bool $applyTax
     * @param bool $applyDiscount
     * @param float $taxRate
     * @param float $discountRate
     * @return array
     */
    public function calculateTotals(array $items, bool $applyTax = true, bool $applyDiscount = false, float $taxRate = 14, float $discountRate = 0): array
    {
        // Calculate subtotal
        $subtotal = collect($items)->sum(function ($item) {
            return (is_numeric($item['quantity']) && is_numeric($item['unit_price'])) 
                ? ($item['quantity'] * $item['unit_price']) 
                : 0;
        });

        // Calculate tax and discount
        $tax = $applyTax ? ($subtotal * ($taxRate / 100)) : 0;
        $discount = $applyDiscount ? ($subtotal * ($discountRate / 100)) : 0;

        // Calculate total
        $total = $subtotal + $tax - $discount;

        // Update individual item totals
        foreach ($items as &$item) {
            $item['total'] = (is_numeric($item['quantity']) && is_numeric($item['unit_price'])) 
                ? ($item['quantity'] * $item['unit_price']) 
                : 0;
        }

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'items' => $items
        ];
    }

    /**
     * Generate DIR requisition number
     *
     * @return string
     */
    public function generateDirNumber(): string
    {
        $lastReq = Requisition::where('requisition_number', 'like', 'DIR%')
            ->orderBy('requisition_number', 'desc')
            ->first();

        if (!$lastReq) {
            return 'DIR00001';
        }

        $lastNumber = (int) substr($lastReq->requisition_number, 3);
        $newNumber = $lastNumber + 1;

        return 'DIR' . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get receiving summary statistics
     *
     * @param string|null $date
     * @param int|null $departmentId
     * @param int|null $supplierId
     * @return array
     */
    public function getReceivingSummary(?string $date = null, ?int $departmentId = null, ?int $supplierId = null): array
    {
        $query = Receiving::query();

        if ($date) {
            $query->whereDate('received_at', $date);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $totalQuantity = $query->sum('quantity');
        $totalValue = $query->sum(\DB::raw('quantity * unit_price'));
        $totalItems = $query->distinct('item_id')->count('item_id');

        $summary = [
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
            'total_items' => $totalItems,
            'average_price' => $totalQuantity > 0 ? $totalValue / $totalQuantity : 0
        ];

        $this->logExecution(__METHOD__, [
            'date' => $date,
            'department_id' => $departmentId,
            'supplier_id' => $supplierId
        ], $summary);

        return $summary;
    }

    /**
     * Get suppliers for search
     *
     * @param string $searchTerm
     * @param int $limit
     * @return Collection
     */
    public function searchSuppliers(string $searchTerm, int $limit = 10): Collection
    {
        if (empty($searchTerm)) {
            return collect();
        }

        return Supplier::where('name', 'like', '%' . $searchTerm . '%')
            ->limit($limit)
            ->get();
    }

    /**
     * Get departments for search
     *
     * @param string $searchTerm
     * @param int $limit
     * @return Collection
     */
    public function searchDepartments(string $searchTerm, int $limit = 10): Collection
    {
        if (empty($searchTerm)) {
            return collect();
        }

        return Department::where('name', 'like', '%' . $searchTerm . '%')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all units
     *
     * @return Collection
     */
    public function getAllUnits(): Collection
    {
        return Unit::all();
    }
}
