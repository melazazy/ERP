<?php

namespace App\Services;

use App\Models\Trust;
use App\Models\Item;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Collection;
use Exception;

class TrustService extends BaseService
{
    /**
     * Create trust records for multiple items
     *
     * @param array $trustData
     * @param array $selectedItems
     * @return array
     * @throws Exception
     */
    public function createTrust(array $trustData, array $selectedItems): array
    {
        return $this->executeTransaction(function () use ($trustData, $selectedItems) {
            $requisitionNumber = $trustData['requisition_number'];
            
            // Check if requisition number already exists
            if (Trust::where('requisition_number', $requisitionNumber)->exists()) {
                throw new Exception('Trust requisition number already exists.');
            }

            $createdTrusts = [];

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

            // Create trust records
            foreach ($selectedItems as $item) {
                $trust = Trust::create([
                    'item_id' => $item['id'],
                    'department_id' => $trustData['department_id'],
                    'quantity' => $item['quantity'],
                    'requested_by' => $trustData['requested_by'],
                    'requisition_number' => $requisitionNumber,
                    'status' => $trustData['status'] ?? 'pending',
                    'requested_date' => $trustData['requested_date'] ?? now()->toDateString(),
                ]);

                $createdTrusts[] = $trust;
            }

            $this->logExecution(__METHOD__, [
                'trust_data' => $trustData,
                'items_count' => count($selectedItems)
            ], ['trust_ids' => collect($createdTrusts)->pluck('id')]);

            return $createdTrusts;
        }, 'Failed to create trust');
    }

    /**
     * Update existing trust
     *
     * @param int $trustId
     * @param array $trustData
     * @return Trust
     * @throws Exception
     */
    public function updateTrust(int $trustId, array $trustData): Trust
    {
        return $this->executeTransaction(function () use ($trustId, $trustData) {
            $trust = Trust::find($trustId);
            $this->validateModelExists($trust, 'Trust not found');

            $trust->update([
                'item_id' => $trustData['item_id'] ?? $trust->item_id,
                'department_id' => $trustData['department_id'] ?? $trust->department_id,
                'quantity' => $trustData['quantity'] ?? $trust->quantity,
                'status' => $trustData['status'] ?? $trust->status,
                'requested_date' => $trustData['requested_date'] ?? $trust->requested_date,
            ]);

            $this->logExecution(__METHOD__, [
                'id' => $trustId,
                'data' => $trustData
            ], $trust->id);

            return $trust;
        }, 'Failed to update trust');
    }

    /**
     * Delete trust
     *
     * @param int $trustId
     * @return bool
     * @throws Exception
     */
    public function deleteTrust(int $trustId): bool
    {
        return $this->executeTransaction(function () use ($trustId) {
            $trust = Trust::find($trustId);
            $this->validateModelExists($trust, 'Trust not found');

            $deleted = $trust->delete();
            $this->logExecution(__METHOD__, ['id' => $trustId], $deleted);
            return $deleted;
        }, 'Failed to delete trust');
    }

    /**
     * Get trusts with filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getTrusts(array $filters = []): Collection
    {
        $query = Trust::with(['item', 'department', 'requester']);

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

        $trusts = $query->orderBy('requested_date', 'desc')->get();

        $this->logExecution(__METHOD__, $filters, ['count' => $trusts->count()]);

        return $trusts;
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
        $totalRequisitions = \App\Models\Requisition::where('item_id', $itemId)->sum('quantity');
        $totalTrusts = Trust::where('item_id', $itemId)->sum('quantity');

        return $totalReceiving - ($totalRequisitions + $totalTrusts);
    }

    /**
     * Get trust summary statistics
     *
     * @param string|null $dateFrom
     * @param string|null $dateTo
     * @param int|null $departmentId
     * @param string|null $status
     * @return array
     */
    public function getTrustSummary(?string $dateFrom = null, ?string $dateTo = null, ?int $departmentId = null, ?string $status = null): array
    {
        $query = Trust::query();

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
        $totalTrusts = $query->count();
        $pendingCount = $query->where('status', 'pending')->count();
        $approvedCount = $query->where('status', 'approved')->count();
        $rejectedCount = $query->where('status', 'rejected')->count();

        $summary = [
            'total_quantity' => $totalQuantity,
            'total_trusts' => $totalTrusts,
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'approval_rate' => $totalTrusts > 0 ? ($approvedCount / $totalTrusts) * 100 : 0
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
     * Search users for trust requests
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
     * Search departments for trust
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
     * Bulk update trust status
     *
     * @param array $trustIds
     * @param string $status
     * @return int
     * @throws Exception
     */
    public function bulkUpdateStatus(array $trustIds, string $status): int
    {
        return $this->executeTransaction(function () use ($trustIds, $status) {
            $validStatuses = ['pending', 'approved', 'rejected'];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Invalid status provided.');
            }

            $updated = Trust::whereIn('id', $trustIds)->update(['status' => $status]);

            $this->logExecution(__METHOD__, [
                'trust_ids' => $trustIds,
                'status' => $status
            ], ['updated_count' => $updated]);

            return $updated;
        }, 'Failed to bulk update trust status');
    }

    /**
     * Get trusts by department
     *
     * @param int $departmentId
     * @param string|null $status
     * @return Collection
     */
    public function getTrustsByDepartment(int $departmentId, ?string $status = null): Collection
    {
        $query = Trust::with(['item', 'requester'])
            ->where('department_id', $departmentId);

        if ($status) {
            $query->where('status', $status);
        }

        $trusts = $query->orderBy('requested_date', 'desc')->get();

        $this->logExecution(__METHOD__, [
            'department_id' => $departmentId,
            'status' => $status
        ], ['count' => $trusts->count()]);

        return $trusts;
    }

    /**
     * Check if item is available for trust
     *
     * @param int $itemId
     * @param float $quantity
     * @return bool
     */
    public function isItemAvailable(int $itemId, float $quantity): bool
    {
        $availableQuantity = $this->calculatePossibleAmount($itemId);
        return $availableQuantity >= $quantity;
    }

    /**
     * Get trust history for an item
     *
     * @param int $itemId
     * @return Collection
     */
    public function getItemTrustHistory(int $itemId): Collection
    {
        $trusts = Trust::with(['department', 'requester'])
            ->where('item_id', $itemId)
            ->orderBy('requested_date', 'desc')
            ->get();

        $this->logExecution(__METHOD__, ['item_id' => $itemId], ['count' => $trusts->count()]);

        return $trusts;
    }
}
