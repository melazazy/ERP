<?php

namespace App\Services;

use App\Models\Requisition;
use App\Models\Department;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Exception;

class TransferService extends BaseService
{
    /**
     * Transfer items between departments
     *
     * @param int $fromDepartmentId
     * @param int $toDepartmentId
     * @param array $selectedItems
     * @return array
     * @throws Exception
     */
    public function transferItems(int $fromDepartmentId, int $toDepartmentId, array $selectedItems): array
    {
        return $this->executeTransaction(function () use ($fromDepartmentId, $toDepartmentId, $selectedItems) {
            // Validate departments are different
            if ($fromDepartmentId === $toDepartmentId) {
                throw new Exception('Source and destination departments must be different.');
            }

            // Check availability for all items
            foreach ($selectedItems as $item) {
                if (!$this->checkAvailability($fromDepartmentId, $item['item_id'], $item['quantity'])) {
                    throw new Exception("Insufficient quantity available for item: {$item['item_name']}");
                }
            }

            $transferNumber = $this->generateTransferNumber();
            $transferredItems = [];

            foreach ($selectedItems as $item) {
                // Find and update source requisition
                $sourceReq = Requisition::where('department_id', $fromDepartmentId)
                    ->where('item_id', $item['item_id'])
                    ->where('unit_id', $item['unit_id'] ?? 1)
                    ->where('quantity', '>=', $item['quantity'])
                    ->first();

                if (!$sourceReq) {
                    throw new Exception("Item no longer available: {$item['item_name']}");
                }

                // Update source quantity
                $newQuantity = $sourceReq->quantity - $item['quantity'];
                if ($newQuantity <= 0) {
                    $sourceReq->delete();
                } else {
                    $sourceReq->update(['quantity' => $newQuantity]);
                }

                // Create new requisition for destination
                $destinationReq = Requisition::create([
                    'requisition_number' => $transferNumber,
                    'item_id' => $item['item_id'],
                    'department_id' => $toDepartmentId,
                    'quantity' => $item['quantity'],
                    'requested_date' => now(),
                    'status' => 'approved',
                    'unit_id' => $item['unit_id'] ?? 1
                ]);

                $transferredItems[] = [
                    'source_requisition' => $sourceReq,
                    'destination_requisition' => $destinationReq,
                    'item' => $item
                ];
            }

            $this->logExecution(__METHOD__, [
                'from_department_id' => $fromDepartmentId,
                'to_department_id' => $toDepartmentId,
                'items_count' => count($selectedItems),
                'transfer_number' => $transferNumber
            ], ['transferred_items' => collect($transferredItems)->pluck('destination_requisition.id')]);

            return [
                'transfer_number' => $transferNumber,
                'transferred_items' => $transferredItems
            ];
        }, 'Failed to transfer items');
    }

    /**
     * Check if item is available for transfer
     *
     * @param int $departmentId
     * @param int $itemId
     * @param float $quantity
     * @return bool
     */
    public function checkAvailability(int $departmentId, int $itemId, float $quantity): bool
    {
        if (!$quantity) {
            return false;
        }

        return Requisition::where('department_id', $departmentId)
            ->where('item_id', $itemId)
            ->where('unit_id', 1)
            ->where('quantity', '>=', $quantity)
            ->exists();
    }

    /**
     * Generate transfer number
     *
     * @return string
     */
    public function generateTransferNumber(): string
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

    /**
     * Get transfer history
     *
     * @param array $filters
     * @return Collection
     */
    public function getTransferHistory(array $filters = []): Collection
    {
        $query = Requisition::with(['item', 'department', 'unit'])
            ->where('requisition_number', 'like', 'TRF%');

        // Apply filters
        if (!empty($filters['from_department'])) {
            $query->where('department_id', $filters['from_department']);
        }

        if (!empty($filters['to_department'])) {
            $query->where('department_id', $filters['to_department']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('requested_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('requested_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['item'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['item'] . '%');
            });
        }

        $transfers = $query->orderBy('requested_date', 'desc')->get();

        $this->logExecution(__METHOD__, $filters, ['count' => $transfers->count()]);

        return $transfers;
    }

    /**
     * Get transfer summary statistics
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param int|null $fromDepartmentId
     * @param int|null $toDepartmentId
     * @return array
     */
    public function getTransferSummary(?string $dateFrom = null, ?string $dateTo = null, ?int $fromDepartmentId = null, ?int $toDepartmentId = null): array
    {
        $query = Requisition::where('requisition_number', 'like', 'TRF%');

        if ($dateFrom) {
            $query->where('requested_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('requested_date', '<=', $dateTo);
        }

        if ($fromDepartmentId) {
            $query->where('department_id', $fromDepartmentId);
        }

        if ($toDepartmentId) {
            $query->where('department_id', $toDepartmentId);
        }

        $totalQuantity = $query->sum('quantity');
        $totalTransfers = $query->count();
        $uniqueTransferNumbers = $query->distinct('requisition_number')->count('requisition_number');

        $summary = [
            'total_quantity' => $totalQuantity,
            'total_transfers' => $totalTransfers,
            'unique_transfer_numbers' => $uniqueTransferNumbers,
            'average_quantity_per_transfer' => $uniqueTransferNumbers > 0 ? $totalQuantity / $uniqueTransferNumbers : 0
        ];

        $this->logExecution(__METHOD__, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'from_department_id' => $fromDepartmentId,
            'to_department_id' => $toDepartmentId
        ], $summary);

        return $summary;
    }

    /**
     * Get departments for transfer
     *
     * @return Collection
     */
    public function getAllDepartments(): Collection
    {
        return Department::all();
    }

    /**
     * Get units for transfer
     *
     * @return Collection
     */
    public function getAllUnits(): Collection
    {
        return Unit::all();
    }

    /**
     * Validate transfer request
     *
     * @param int $fromDepartmentId
     * @param int $toDepartmentId
     * @param array $selectedItems
     * @return array
     */
    public function validateTransfer(int $fromDepartmentId, int $toDepartmentId, array $selectedItems): array
    {
        $errors = [];
        $warnings = [];

        // Check if departments are different
        if ($fromDepartmentId === $toDepartmentId) {
            $errors[] = 'Source and destination departments must be different.';
        }

        // Check if items are selected
        if (empty($selectedItems)) {
            $errors[] = 'Please select at least one item for transfer.';
        }

        // Check availability for each item
        foreach ($selectedItems as $item) {
            if (empty($item['quantity']) || $item['quantity'] <= 0) {
                $errors[] = "Invalid quantity for item: {$item['item_name']}";
                continue;
            }

            if (!$this->checkAvailability($fromDepartmentId, $item['item_id'], $item['quantity'])) {
                $errors[] = "Insufficient quantity available for item: {$item['item_name']}";
            }
        }

        // Check if transfer would result in negative stock
        foreach ($selectedItems as $item) {
            $currentStock = $this->getCurrentStock($fromDepartmentId, $item['item_id']);
            if ($currentStock < $item['quantity']) {
                $warnings[] = "Transfer may result in low stock for item: {$item['item_name']} (Current: {$currentStock})";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
    }

    /**
     * Get current stock for an item in a department
     *
     * @param int $departmentId
     * @param int $itemId
     * @return float
     */
    public function getCurrentStock(int $departmentId, int $itemId): float
    {
        $totalReceiving = \App\Models\Receiving::where('department_id', $departmentId)
            ->where('item_id', $itemId)
            ->sum('quantity');

        $totalRequisitions = Requisition::where('department_id', $departmentId)
            ->where('item_id', $itemId)
            ->sum('quantity');

        $totalTrusts = \App\Models\Trust::where('department_id', $departmentId)
            ->where('item_id', $itemId)
            ->sum('quantity');

        return $totalReceiving - ($totalRequisitions + $totalTrusts);
    }

    /**
     * Reverse a transfer
     *
     * @param string $transferNumber
     * @return bool
     * @throws Exception
     */
    public function reverseTransfer(string $transferNumber): bool
    {
        return $this->executeTransaction(function () use ($transferNumber) {
            $transferRequisitions = Requisition::where('requisition_number', $transferNumber)->get();

            if ($transferRequisitions->isEmpty()) {
                throw new Exception('Transfer not found.');
            }

            foreach ($transferRequisitions as $transferReq) {
                // Delete the transfer requisition
                $transferReq->delete();

                // Optionally, you could restore the original source requisition here
                // This would require storing additional information about the source
            }

            $this->logExecution(__METHOD__, ['transfer_number' => $transferNumber], true);

            return true;
        }, 'Failed to reverse transfer');
    }
}
