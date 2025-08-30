<?php

namespace App\Services;

use App\Models\Requisition;
use App\Models\Item;
use App\Models\Department;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Exception;

class RequisitionService extends BaseService
{
    /**
     * Create requisition records for multiple items
     *
     * @param array $requisitionData
     * @param array $selectedItems
     * @return array
     * @throws Exception
     */
    public function createRequisition(array $requisitionData, array $selectedItems): array
    {
        return $this->executeTransaction(function () use ($requisitionData, $selectedItems) {
            $requisitionNumber = $requisitionData['requisition_number'];
            
            // Check if requisition number already exists
            if (Requisition::where('requisition_number', $requisitionNumber)->exists()) {
                throw new Exception('Requisition number already exists.');
            }

            $createdRequisitions = [];

            // Validate stock availability for all items
            $errors = [];
            foreach ($selectedItems as $item) {
                $possibleAmount = $this->calculatePossibleAmount($item['id']);

                if ($item['quantity'] > $possibleAmount) {
                    $errors[] = 'Exceeded possible amount for item: ' . $item['name'] .
                        ' (Available: ' . $possibleAmount . ')';
                }
            }

            if (!empty($errors)) {
                throw new Exception(implode('<br>', $errors));
            }

            // Create requisition records
            foreach ($selectedItems as $item) {
                $requisition = Requisition::create([
                    'item_id' => $item['id'],
                    'requested_date' => $requisitionData['requested_date'] ?? now()->toDateString(),
                    'department_id' => $requisitionData['department_id'],
                    'quantity' => $item['quantity'],
                    'requested_by' => $requisitionData['requested_by'],
                    'requisition_number' => $requisitionNumber,
                    'status' => $requisitionData['status'] ?? 'pending',
                    'unit_id' => $item['unit_id'] ?? 1
                ]);

                $createdRequisitions[] = $requisition;
            }

            $this->logExecution(__METHOD__, [
                'requisition_data' => $requisitionData,
                'items_count' => count($selectedItems)
            ], ['requisition_ids' => collect($createdRequisitions)->pluck('id')]);

            return $createdRequisitions;
        }, 'Failed to create requisition');
    }

    /**
     * Update existing requisition
     *
     * @param int $requisitionId
     * @param array $requisitionData
     * @return Requisition
     * @throws Exception
     */
    public function updateRequisition(int $requisitionId, array $requisitionData): Requisition
    {
        return $this->executeTransaction(function () use ($requisitionId, $requisitionData) {
            $requisition = Requisition::find($requisitionId);
            $this->validateModelExists($requisition, 'Requisition not found');

            $requisition->update([
                'item_id' => $requisitionData['item_id'] ?? $requisition->item_id,
                'department_id' => $requisitionData['department_id'] ?? $requisition->department_id,
                'quantity' => $requisitionData['quantity'] ?? $requisition->quantity,
                'status' => $requisitionData['status'] ?? $requisition->status,
                'unit_id' => $requisitionData['unit_id'] ?? $requisition->unit_id
            ]);

            $this->logExecution(__METHOD__, [
                'id' => $requisitionId,
                'data' => $requisitionData
            ], $requisition->id);

            return $requisition;
        }, 'Failed to update requisition');
    }

    /**
     * Delete requisition
     *
     * @param int $requisitionId
     * @return bool
     * @throws Exception
     */
    public function deleteRequisition(int $requisitionId): bool
    {
        return $this->executeTransaction(function () use ($requisitionId) {
            $requisition = Requisition::find($requisitionId);
            $this->validateModelExists($requisition, 'Requisition not found');

            $deleted = $requisition->delete();
            $this->logExecution(__METHOD__, ['id' => $requisitionId], $deleted);
            return $deleted;
        }, 'Failed to delete requisition');
    }

    /**
     * Get requisitions with filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getRequisitions(array $filters = []): Collection
    {
        $query = Requisition::with(['item', 'department', 'requester', 'unit']);

        // Apply filters
        if (!empty($filters['item'])) {
            $query->whereHas('item', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['item'] . '%');
            });
        }

        if (!empty($filters['department'])) {
            $query->whereHas('department', function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['department'] . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', 'like', '%' . $filters['status'] . '%');
        }

        if (!empty($filters['date_from'])) {
            $query->where('requested_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('requested_date', '<=', $filters['date_to']);
        }

        $requisitions = $query->orderBy('requested_date', 'desc')->get();

        $this->logExecution(__METHOD__, $filters, ['count' => $requisitions->count()]);

        return $requisitions;
    }

    /**
     * Calculate possible amount for an item
     *
     * @param int $itemId
     * @return float
     */
    public function calculatePossibleAmount(int $itemId): float
    {
        $totalReceiving = \App\Models\Receiving::where('item_id', $itemId)->sum('quantity');
        $totalRequisitions = Requisition::where('item_id', $itemId)->sum('quantity');
        $totalTrusts = \App\Models\Trust::where('item_id', $itemId)->sum('quantity');

        return $totalReceiving - ($totalRequisitions + $totalTrusts);
    }

    /**
     * Generate next requisition number
     *
     * @param string $prefix
     * @return string
     */
    public function generateRequisitionNumber(string $prefix = 'REQ'): string
    {
        $lastReq = Requisition::where('requisition_number', 'like', $prefix . '%')
            ->orderBy('requisition_number', 'desc')
            ->first();

        if (!$lastReq) {
            return $prefix . '00001';
        }

        $lastNumber = (int) substr($lastReq->requisition_number, strlen($prefix));
        $newNumber = $lastNumber + 1;

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get requisition summary statistics
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param int|null $departmentId
     * @param string|null $status
     * @return array
     */
    public function getRequisitionSummary(?string $dateFrom = null, ?string $dateTo = null, ?int $departmentId = null, ?string $status = null): array
    {
        $query = Requisition::query();

        if ($dateFrom) {
            $query->where('requested_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('requested_date', '<=', $dateTo);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $totalQuantity = $query->sum('quantity');
        $totalRequisitions = $query->count();
        $pendingCount = $query->where('status', 'pending')->count();
        $approvedCount = $query->where('status', 'approved')->count();
        $rejectedCount = $query->where('status', 'rejected')->count();

        $summary = [
            'total_quantity' => $totalQuantity,
            'total_requisitions' => $totalRequisitions,
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'approval_rate' => $totalRequisitions > 0 ? ($approvedCount / $totalRequisitions) * 100 : 0
        ];

        $this->logExecution(__METHOD__, [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'department_id' => $departmentId,
            'status' => $status
        ], $summary);

        return $summary;
    }

    /**
     * Search users for requisition requests
     *
     * @param string $searchTerm
     * @param int $limit
     * @return Collection
     */
    public function searchUsers(string $searchTerm, int $limit = 10): Collection
    {
        if (empty($searchTerm)) {
            return collect();
        }

        return User::where('name', 'like', '%' . $searchTerm . '%')
            ->limit($limit)
            ->get();
    }

    /**
     * Search departments for requisition
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

    /**
     * Bulk update requisition status
     *
     * @param array $requisitionIds
     * @param string $status
     * @return int
     * @throws Exception
     */
    public function bulkUpdateStatus(array $requisitionIds, string $status): int
    {
        return $this->executeTransaction(function () use ($requisitionIds, $status) {
            $validStatuses = ['pending', 'approved', 'rejected'];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid status provided.');
            }

            $updated = Requisition::whereIn('id', $requisitionIds)->update(['status' => $status]);

            $this->logExecution(__METHOD__, [
                'requisition_ids' => $requisitionIds,
                'status' => $status
            ], ['updated_count' => $updated]);

            return $updated;
        }, 'Failed to bulk update requisition status');
    }

    /**
     * Get requisitions by department
     *
     * @param int $departmentId
     * @param string|null $status
     * @return Collection
     */
    public function getRequisitionsByDepartment(int $departmentId, ?string $status = null): Collection
    {
        $query = Requisition::with(['item', 'requester', 'unit'])
            ->where('department_id', $departmentId);

        if ($status) {
            $query->where('status', $status);
        }

        $requisitions = $query->orderBy('requested_date', 'desc')->get();

        $this->logExecution(__METHOD__, [
            'department_id' => $departmentId,
            'status' => $status
        ], ['count' => $requisitions->count()]);

        return $requisitions;
    }
}
