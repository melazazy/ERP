<?php

namespace App\Services;

use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;
use App\Models\Item;
use App\Models\Department;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class ReportingService extends BaseService
{
    /**
     * Get inventory reports with filters
     *
     * @param array $filters
     * @return array
     */
    public function getInventoryReport(array $filters = []): array
    {
        $query = Item::query()
            ->with('subcategory.category')
            ->withSum('receivings', 'quantity')
            ->withSum('requisitions', 'quantity')
            ->withSum('trusts', 'quantity')
            ->withCount(['receivings', 'requisitions', 'trusts']);

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['category'])) {
            $query->whereHas('subcategory.category', function ($q) use ($filters) {
                $q->where('id', $filters['category']);
            });
        }

        if (!empty($filters['subcategory'])) {
            $query->where('subcategory_id', $filters['subcategory']);
        }

        $items = $query->orderBy('name')->paginate($filters['per_page'] ?? 10);

        // Calculate net quantity for each item
        foreach ($items as $item) {
            $item->net_quantity = ($item->receivings_sum_quantity ?? 0) - 
                                ($item->requisitions_sum_quantity ?? 0) - 
                                ($item->trusts_sum_quantity ?? 0);
        }

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

        $totalQuantity = $totals->net_quantity ?? 0;

        $this->logExecution(__METHOD__, $filters, [
            'items_count' => $items->count(),
            'total_quantity' => $totalQuantity
        ]);

        return [
            'items' => $items,
            'total_quantity' => $totalQuantity,
            'categories' => Category::all(),
            'subcategories' => Subcategory::all()
        ];
    }

    /**
     * Get department report
     *
     * @param int $departmentId
     * @param string $dateFrom
     * @param string $dateTo
     * @param string $docType
     * @param string|null $docNumber
     * @return array
     */
    public function getDepartmentReport(int $departmentId, string $dateFrom, string $dateTo, string $docType = 'all', ?string $docNumber = null): array
    {
        $receivings = collect();
        $requisitions = collect();
        $totalReceivings = 0;
        $totalRequisitions = 0;

        if ($docType === 'all' || $docType === 'receiving') {
            $receivingQuery = Receiving::with(['item', 'supplier'])
                ->where('department_id', $departmentId)
                ->whereBetween('received_at', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ]);

            if ($docNumber) {
                $receivingQuery->where('receiving_number', $docNumber);
            }

            $receivings = $receivingQuery->get();
            $totalReceivings = $receivings->sum('quantity');
        }

        if ($docType === 'all' || $docType === 'requisition') {
            $requisitionQuery = Requisition::with(['item', 'requester'])
                ->where('department_id', $departmentId)
                ->whereBetween('requested_date', [
                    Carbon::parse($dateFrom)->startOfDay(),
                    Carbon::parse($dateTo)->endOfDay()
                ]);

            if ($docNumber) {
                $requisitionQuery->where('requisition_number', $docNumber);
            }

            $requisitions = $requisitionQuery->get();
            $totalRequisitions = $requisitions->sum('quantity');
        }

        $this->logExecution(__METHOD__, [
            'department_id' => $departmentId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'doc_type' => $docType,
            'doc_number' => $docNumber
        ], [
            'receivings_count' => $receivings->count(),
            'requisitions_count' => $requisitions->count(),
            'total_receivings' => $totalReceivings,
            'total_requisitions' => $totalRequisitions
        ]);

        return [
            'receivings' => $receivings,
            'requisitions' => $requisitions,
            'total_receivings' => $totalReceivings,
            'total_requisitions' => $totalRequisitions
        ];
    }

    /**
     * Get supplier report
     *
     * @param int $supplierId
     * @param string $dateFrom
     * @param string $dateTo
     * @param string|null $docNumber
     * @return array
     */
    public function getSupplierReport(int $supplierId, string $dateFrom, string $dateTo, ?string $docNumber = null): array
    {
        $supplier = Supplier::find($supplierId);
        if (!$supplier) {
            throw new Exception('Supplier not found.');
        }

        $query = Receiving::with(['item', 'unit', 'department'])
            ->where('supplier_id', $supplierId)
            ->whereBetween('received_at', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay()
            ]);

        if ($docNumber) {
            $query->where('receiving_number', 'like', '%' . $docNumber . '%');
        }

        $receivings = $query->get();
        $totalAmount = $receivings->sum(function($receiving) {
            return ($receiving->unit_price ?? $receiving->price ?? 0) * $receiving->quantity;
        });

        $this->logExecution(__METHOD__, [
            'supplier_id' => $supplierId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'doc_number' => $docNumber
        ], [
            'receivings_count' => $receivings->count(),
            'total_amount' => $totalAmount
        ]);

        return [
            'supplier' => $supplier,
            'receivings' => $receivings,
            'total_amount' => $totalAmount,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];
    }

    /**
     * Get dashboard statistics
     *
     * @param string $roleName
     * @return array
     */
    public function getDashboardStats(string $roleName): array
    {
        $stats = [];

        switch (strtolower($roleName)) {
            case 'system administrator':
            case 'warehouse manager':
                $stats = $this->getFullDashboardStats();
                break;
            case 'receiving clerk':
                $stats = $this->getReceivingClerkStats();
                break;
            case 'requisition clerk':
                $stats = $this->getRequisitionClerkStats();
                break;
            case 'trust clerk':
                $stats = $this->getTrustClerkStats();
                break;
            case 'inventory controller':
                $stats = $this->getInventoryControllerStats();
                break;
            default:
                $stats = $this->getBasicDashboardStats();
        }

        $this->logExecution(__METHOD__, ['role' => $roleName], $stats);

        return $stats;
    }

    /**
     * Get full dashboard statistics for administrators
     *
     * @return array
     */
    private function getFullDashboardStats(): array
    {
        $totalItems = Item::count();
        $totalDepartments = Department::count();
        $totalSuppliers = Supplier::count();

        $todayReceivings = Receiving::whereDate('received_at', today())->sum('quantity');
        $todayRequisitions = Requisition::whereDate('requested_date', today())->sum('quantity');
        $todayTrusts = Trust::whereDate('requested_date', today())->sum('quantity');

        $pendingRequisitions = Requisition::where('status', 'pending')->count();
        $pendingTrusts = Trust::where('status', 'pending')->count();

        return [
            'total_items' => $totalItems,
            'total_departments' => $totalDepartments,
            'total_suppliers' => $totalSuppliers,
            'today_receivings' => $todayReceivings,
            'today_requisitions' => $todayRequisitions,
            'today_trusts' => $todayTrusts,
            'pending_requisitions' => $pendingRequisitions,
            'pending_trusts' => $pendingTrusts
        ];
    }

    /**
     * Get basic dashboard statistics
     *
     * @return array
     */
    private function getBasicDashboardStats(): array
    {
        return [
            'total_items' => Item::count(),
            'total_departments' => Department::count()
        ];
    }

    /**
     * Get receiving clerk statistics
     *
     * @return array
     */
    private function getReceivingClerkStats(): array
    {
        $todayReceivings = Receiving::whereDate('received_at', today())->sum('quantity');
        $todayReceivingsCount = Receiving::whereDate('received_at', today())->count();

        return [
            'today_receivings' => $todayReceivings,
            'today_receivings_count' => $todayReceivingsCount
        ];
    }

    /**
     * Get requisition clerk statistics
     *
     * @return array
     */
    private function getRequisitionClerkStats(): array
    {
        $todayRequisitions = Requisition::whereDate('requested_date', today())->sum('quantity');
        $pendingRequisitions = Requisition::where('status', 'pending')->count();

        return [
            'today_requisitions' => $todayRequisitions,
            'pending_requisitions' => $pendingRequisitions
        ];
    }

    /**
     * Get trust clerk statistics
     *
     * @return array
     */
    private function getTrustClerkStats(): array
    {
        $todayTrusts = Trust::whereDate('requested_date', today())->sum('quantity');
        $pendingTrusts = Trust::where('status', 'pending')->count();

        return [
            'today_trusts' => $todayTrusts,
            'pending_trusts' => $pendingTrusts
        ];
    }

    /**
     * Get inventory controller statistics
     *
     * @return array
     */
    private function getInventoryControllerStats(): array
    {
        $totalItems = Item::count();
        $lowStockItems = $this->getLowStockItems();

        return [
            'total_items' => $totalItems,
            'low_stock_items' => $lowStockItems
        ];
    }

    /**
     * Get low stock items
     *
     * @return Collection
     */
    private function getLowStockItems(): Collection
    {
        return Item::with(['subcategory.category'])
            ->get()
            ->filter(function ($item) {
                $availableStock = $this->calculateAvailableStock($item->id);
                return $availableStock <= 10; // Threshold for low stock
            })
            ->values();
    }

    /**
     * Calculate available stock for an item
     *
     * @param int $itemId
     * @return float
     */
    private function calculateAvailableStock(int $itemId): float
    {
        $totalReceiving = Receiving::where('item_id', $itemId)->sum('quantity');
        $totalRequisitions = Requisition::where('item_id', $itemId)->sum('quantity');
        $totalTrusts = Trust::where('item_id', $itemId)->sum('quantity');

        return $totalReceiving - ($totalRequisitions + $totalTrusts);
    }
}
