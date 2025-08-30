<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;
use App\Models\Department;
use Illuminate\Support\Carbon;
use App\Models\Item;

class DashboardReports extends Component
{
    public function render()
    {
        $user = Auth::user();
        $roleName = $user && $user->role ? $user->role->name : 'User';
        
        // Get role-based permissions
        $permissions = $this->getRolePermissions($roleName);
        
        // Get numbers based on permissions
        $numbers = $this->getNumbers($roleName, $permissions);
        
        // Get charts based on permissions
        $charts = $this->getCircularCharts($numbers, $permissions);

        return view('livewire/dashboard-reports', [
            'numbers' => $numbers,
            'charts' => $charts,
            'user' => $user,
            'role' => $roleName,
            'permissions' => $permissions,
        ]);
    }

    private function getRolePermissions(string $roleName): array
    {
        $normalized = trim(mb_strtolower($roleName));
        
        $permissions = [
            'can_view_totals' => false,
            'can_view_department_breakdown' => false,
            'can_view_receivings' => false,
            'can_view_requisitions' => false,
            'can_view_trusts' => false,
            'can_view_users' => false,
            'can_view_financial_data' => false,
            'can_view_operational_data' => false,
            'can_view_all_departments' => false,
            'can_view_own_department' => false,
            'can_view_charts' => false,
        ];

        switch ($normalized) {
            case 'system administrator':
                $permissions = array_fill_keys(array_keys($permissions), true);
                break;
                
            case 'warehouse manager':
                $permissions['can_view_totals'] = true;
                $permissions['can_view_department_breakdown'] = true;
                $permissions['can_view_receivings'] = true;
                $permissions['can_view_requisitions'] = true;
                $permissions['can_view_trusts'] = true;
                $permissions['can_view_financial_data'] = true;
                $permissions['can_view_operational_data'] = true;
                $permissions['can_view_all_departments'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'receiving clerk':
                $permissions['can_view_receivings'] = true;
                $permissions['can_view_own_department'] = true;
                $permissions['can_view_operational_data'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'requisition clerk':
                $permissions['can_view_requisitions'] = true;
                $permissions['can_view_own_department'] = true;
                $permissions['can_view_operational_data'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'trust clerk':
                $permissions['can_view_trusts'] = true;
                $permissions['can_view_own_department'] = true;
                $permissions['can_view_operational_data'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'inventory controller':
                $permissions['can_view_totals'] = true;
                $permissions['can_view_department_breakdown'] = true;
                $permissions['can_view_receivings'] = true;
                $permissions['can_view_requisitions'] = true;
                $permissions['can_view_trusts'] = true;
                $permissions['can_view_financial_data'] = true;
                $permissions['can_view_all_departments'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'store keeper':
                $permissions['can_view_receivings'] = true;
                $permissions['can_view_own_department'] = true;
                $permissions['can_view_operational_data'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'accountant':
                $permissions['can_view_totals'] = true;
                $permissions['can_view_financial_data'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'auditor':
                $permissions['can_view_totals'] = true;
                $permissions['can_view_department_breakdown'] = true;
                $permissions['can_view_receivings'] = true;
                $permissions['can_view_requisitions'] = true;
                $permissions['can_view_trusts'] = true;
                $permissions['can_view_financial_data'] = true;
                $permissions['can_view_all_departments'] = true;
                $permissions['can_view_charts'] = true;
                break;
                
            case 'department manager':
                $permissions['can_view_own_department'] = true;
                $permissions['can_view_operational_data'] = true;
                $permissions['can_view_charts'] = true;
                break;
        }

        return $permissions;
    }

    private function getNumbers(string $roleName, array $permissions): array
    {
        $numbers = [
            'totals' => [],
            'perDept' => [],
            'user_department' => null,
            'role_specific' => []
        ];

        // Get user's department if they can only view their own
        if ($permissions['can_view_own_department'] && !$permissions['can_view_all_departments']) {
            $user = Auth::user();
            $numbers['user_department'] = $user->department_id ?? null;
        }

        // Get FIFO unit prices for items from most recent receivings
        $itemUnitPrices = Receiving::selectRaw('item_id, unit_price, created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(function ($receivings) {
                return $receivings->first()->unit_price;
            });

        // Build totals based on permissions
        if ($permissions['can_view_receivings']) {
            $numbers['totals']['receivings'] = Receiving::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')->value('total') ?? 0;
        }

        if ($permissions['can_view_requisitions']) {
            $numbers['totals']['requisitions'] = $this->calculateFifoAmount(Requisition::class, $itemUnitPrices);
        }

        if ($permissions['can_view_trusts']) {
            $numbers['totals']['trusts'] = $this->calculateFifoAmount(Trust::class, $itemUnitPrices);
        }

        if ($permissions['can_view_users']) {
            $numbers['totals']['users'] = User::count();
        }

        // Build department breakdown based on permissions
        if ($permissions['can_view_department_breakdown']) {
            $deptNamesById = Department::query()->pluck('name', 'id');
            
            $receivingsByDept = Receiving::selectRaw('department_id, COALESCE(SUM(quantity * unit_price),0) as total')
                ->groupBy('department_id')->pluck('total', 'department_id');
            $requisitionsByDept = $this->calculateFifoAmountByDepartment(Requisition::class, $itemUnitPrices);
            $trustsByDept = $this->calculateFifoAmountByDepartment(Trust::class, $itemUnitPrices);

            $perDept = [];
            foreach ($deptNamesById as $deptId => $deptName) {
                // Skip if user can only view their own department
                if ($permissions['can_view_own_department'] && !$permissions['can_view_all_departments']) {
                    if ($deptId != $numbers['user_department']) {
                        continue;
                    }
                }

                $received = (float) ($receivingsByDept[$deptId] ?? 0);
                $req = (float) ($requisitionsByDept[$deptId] ?? 0);
                $trs = (float) ($trustsByDept[$deptId] ?? 0);
                
                $perDept[] = [
                    'department' => $deptName,
                    'remaining' => max(0, $received - $req - $trs),
                    'received' => (int) $received,
                    'requisitioned' => (int) $req,
                    'trusted' => (int) $trs,
                ];
            }

            usort($perDept, fn($a, $b) => $b['remaining'] <=> $a['remaining']);
            $numbers['perDept'] = array_slice($perDept, 0, 10);
        }

        // Add role-specific reports
        $numbers['role_specific'] = $this->getRoleSpecificReports($roleName, $permissions, $itemUnitPrices);

        return $numbers;
    }

    private function getRoleSpecificReports(string $roleName, array $permissions, $itemUnitPrices): array
    {
        $normalized = trim(mb_strtolower($roleName));
        $reports = [];

        switch ($normalized) {
            case 'system administrator':
                $reports = [
                    'pending_approvals' => Requisition::where('status', 'pending')->count(),
                    'low_stock_items' => $this->getLowStockItemsCount(),
                    'recent_activities' => $this->getRecentActivitiesCount(),
                    'system_health' => $this->getSystemHealthMetrics(),
                ];
                break;

            case 'warehouse manager':
                $reports = [
                    'total_receivings' => Receiving::count(),
                    'low_stock_alerts' => $this->getLowStockItemsCount(),
                    'overstock_items' => $this->getOverstockItemsCount(),
                    'warehouse_efficiency' => $this->getWarehouseEfficiencyMetrics(),
                ];
                break;

            case 'receiving clerk':
                $reports = [
                    'today_receivings' => Receiving::whereDate('created_at', today())->count(),
                    'total_receivings' => Receiving::count(),
                    'my_department_receivings' => $this->getUserDepartmentReceivings(),
                    'receiving_trends' => $this->getReceivingTrends(),
                ];
                break;

            case 'requisition clerk':
                $reports = [
                    'my_requisitions' => Requisition::where('requested_by', auth()->id())->count(),
                    'pending_my_requisitions' => Requisition::where('requested_by', auth()->id())->where('status', 'pending')->count(),
                    'approved_requisitions' => Requisition::where('requested_by', auth()->id())->where('status', 'approved')->count(),
                    'requisition_history' => $this->getRequisitionHistory(),
                ];
                break;

            case 'trust clerk':
                $reports = [
                    'my_trusts' => Trust::where('requested_by', auth()->id())->count(),
                    'total_trusts' => Trust::count(),
                    'trust_return_rate' => $this->getTrustReturnRate(),
                    'trust_trends' => $this->getTrustTrends(),
                ];
                break;

            case 'inventory controller':
                $reports = [
                    'critical_stock_items' => $this->getCriticalStockItemsCount(),
                    'expired_items' => $this->getExpiredItemsCount(),
                    'inventory_turnover' => $this->getInventoryTurnoverRate(),
                    'stock_valuation' => $this->getStockValuationMetrics(),
                ];
                break;

            case 'store keeper':
                $reports = [
                    'my_department_stock' => $this->getUserDepartmentStock(),
                    'items_needing_restock' => $this->getItemsNeedingRestock(),
                    'storage_utilization' => $this->getStorageUtilization(),
                    'daily_operations' => $this->getDailyOperationsMetrics(),
                ];
                break;

            case 'accountant':
                $reports = [
                    'total_inventory_value' => $this->getTotalInventoryValue($itemUnitPrices),
                    'monthly_expenses' => $this->getMonthlyExpenses(),
                    'cost_analysis' => $this->getCostAnalysisMetrics(),
                    'budget_vs_actual' => $this->getBudgetVsActualMetrics(),
                ];
                break;

            case 'auditor':
                $reports = [
                    'audit_trail_items' => $this->getAuditTrailItems(),
                    'discrepancy_reports' => $this->getDiscrepancyReports(),
                    'compliance_metrics' => $this->getComplianceMetrics(),
                    'risk_assessment' => $this->getRiskAssessmentMetrics(),
                ];
                break;

            case 'department manager':
                $reports = [
                    'department_performance' => $this->getDepartmentPerformance(),
                    'team_activity' => $this->getTeamActivityMetrics(),
                    'resource_utilization' => $this->getResourceUtilization(),
                    'department_efficiency' => $this->getDepartmentEfficiencyMetrics(),
                ];
                break;
        }

        return $reports;
    }

    private function getItemUnitPrices()
    {
        // Get FIFO unit prices for items from most recent receivings
        return Receiving::selectRaw('item_id, unit_price, created_at')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('item_id')
            ->map(function ($receivings) {
                return $receivings->first()->unit_price;
            });
    }

    private function getTotalInventoryValue($itemUnitPrices): float
    {
        $items = Receiving::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->get();

        $totalValue = 0;
        foreach ($items as $item) {
            $unitPrice = $itemUnitPrices[$item->item_id] ?? 0;
            $totalValue += $item->total_quantity * $unitPrice;
        }

        return $totalValue;
    }

    private function calculateFifoAmount(string $modelClass, $itemUnitPrices): float
    {
        $items = $modelClass::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->get();

        $totalAmount = 0;
        foreach ($items as $item) {
            $unitPrice = $itemUnitPrices[$item->item_id] ?? 0;
            $totalAmount += $item->total_quantity * $unitPrice;
        }

        return $totalAmount;
    }

    private function calculateFifoAmountByDepartment(string $modelClass, $itemUnitPrices): array
    {
        $items = $modelClass::selectRaw('item_id, department_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id', 'department_id')
            ->get();

        $deptAmounts = [];
        foreach ($items as $item) {
            $unitPrice = $itemUnitPrices[$item->item_id] ?? 0;
            $amount = $item->total_quantity * $unitPrice;
            
            if (!isset($deptAmounts[$item->department_id])) {
                $deptAmounts[$item->department_id] = 0;
            }
            $deptAmounts[$item->department_id] += $amount;
        }

        return $deptAmounts;
    }

    private function getCircularCharts(array $numbers, array $permissions): array
    {
        if (!$permissions['can_view_charts']) {
                return [];
        }

        $charts = [];
        $totals = $numbers['totals'] ?? [];
        $perDept = $numbers['perDept'] ?? [];

        // Totals Doughnut Chart - only if user can view totals
        if ($permissions['can_view_totals'] && count($totals) > 0) {
            $chartData = [];
            $chartLabels = [];
            
            if (isset($totals['receivings'])) {
                $chartData[] = $totals['receivings'];
                $chartLabels[] = 'Receivings';
            }
            if (isset($totals['requisitions'])) {
                $chartData[] = $totals['requisitions'];
                $chartLabels[] = 'Requisitions';
            }
            if (isset($totals['trusts'])) {
                $chartData[] = $totals['trusts'];
                $chartLabels[] = 'Trusts';
            }

            if (count($chartData) > 0) {
                $charts[] = [
                    'id' => 'totalDoughnut',
                    'type' => 'doughnut',
                    'title' => 'Totals Overview',
                    'labels' => $chartLabels,
                    'datasets' => [[
                        'label' => 'Totals',
                        'data' => $chartData,
                        'backgroundColor' => ['#6366F1', '#F59E0B', '#10B981'],
                    ]],
                ];
            }
        }

        // Department Charts - only if user can view department breakdown
        if ($permissions['can_view_department_breakdown'] && count($perDept) > 0) {
            $deptLabels = [];
            $deptReceived = [];
            $deptRequisitioned = [];
            $deptTrusted = [];
            
            foreach ($perDept as $item) {
                $deptLabels[] = $item['department'];
                $deptReceived[] = $item['received'];
                $deptRequisitioned[] = $item['requisitioned'];
                $deptTrusted[] = $item['trusted'];
            }

            if ($permissions['can_view_receivings']) {
                $charts[] = [
                    'id' => 'deptReceivedDoughnut',
                    'type' => 'doughnut',
                    'title' => 'Received Stock by Department',
                    'labels' => $deptLabels,
                    'datasets' => [[
                        'label' => 'Received',
                        'data' => $deptReceived,
                        'backgroundColor' => ['#6366F1', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#FDE68A', '#60A5FA', '#10B981', '#3B82F6'],
                    ]],
                ];
            }

            if ($permissions['can_view_requisitions']) {
                $charts[] = [
                    'id' => 'deptRequisitionedDoughnut',
                    'type' => 'doughnut',
                    'title' => 'Requisitioned Stock by Department',
                    'labels' => $deptLabels,
                    'datasets' => [[
                        'label' => 'Requisitioned',
                        'data' => $deptRequisitioned,
                        'backgroundColor' => ['#6366F1', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#FDE68A', '#60A5FA', '#10B981', '#3B82F6'],
                    ]],
                ];
            }

            if ($permissions['can_view_trusts']) {
                $charts[] = [
                    'id' => 'deptTrustedDoughnut',
                    'type' => 'doughnut',
                    'title' => 'Trusted Stock by Department',
                    'labels' => $deptLabels,
                    'datasets' => [[
                        'label' => 'Trusted',
                        'data' => $deptTrusted,
                        'backgroundColor' => ['#6366F1', '#F59E0B', '#10B981', '#3B82F6', '#8B5CF6', '#EC4899', '#FDE68A', '#60A5FA', '#10B981', '#3B82F6'],
                    ]],
                ];
            }
        }

        return $charts;
    }

    private function getLowStockItemsCount(): int
    {
        // Get items with low stock (less than 10 units)
        $lowStockItems = Receiving::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('SUM(quantity) < 10')
            ->count();
        
        return $lowStockItems;
    }

    private function getRecentActivitiesCount(): int
    {
        // Count recent activities (last 7 days)
        $recentDate = now()->subDays(7);
        return Receiving::where('created_at', '>=', $recentDate)->count() +
               Requisition::where('created_at', '>=', $recentDate)->count() +
               Trust::where('created_at', '>=', $recentDate)->count();
    }

    private function getSystemHealthMetrics(): float
    {
        // Simple system health calculation based on data consistency
        $totalItems = Item::count();
        $totalReceivings = Receiving::count();
        $totalRequisitions = Requisition::count();
        
        if ($totalItems == 0) return 0;
        
        // Calculate health score (0-100)
        $healthScore = min(100, ($totalReceivings + $totalRequisitions) / $totalItems * 10);
        return round($healthScore, 1);
    }

    private function getOverstockItemsCount(): int
    {
        // Get items with overstock (more than 100 units)
        $overstockItems = Receiving::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('SUM(quantity) > 100')
            ->count();
        
        return $overstockItems;
    }

    private function getWarehouseEfficiencyMetrics(): float
    {
        // Calculate warehouse efficiency based on receiving vs requisition ratio
        $totalReceived = Receiving::sum('quantity');
        $totalRequisitioned = Requisition::sum('quantity');
        
        if ($totalReceived == 0) return 0;
        
        $efficiency = ($totalRequisitioned / $totalReceived) * 100;
        return round(min(100, $efficiency), 1);
    }

    private function getUserDepartmentReceivings(): int
    {
        $user = Auth::user();
        if (!$user || !$user->department_id) return 0;
        
        return Receiving::where('department_id', $user->department_id)->count();
    }

    private function getReceivingTrends(): array
    {
        // Get receiving trends for last 6 months
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Receiving::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $trends[$month->format('M Y')] = $count;
        }
        return $trends;
    }

    private function getRequisitionHistory(): array
    {
        // Get requisition history for current user
        $user = Auth::user();
        if (!$user) return [];
        
        $history = Requisition::where('requested_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($req) {
                return [
                    'item' => $req->item->name ?? 'Unknown',
                    'quantity' => $req->quantity,
                    'status' => $req->status,
                    'date' => $req->created_at->format('M d, Y')
                ];
            })
            ->toArray();
        
        return $history;
    }

    private function getTrustReturnRate(): float
    {
        // Calculate trust return rate (simplified)
        $totalTrusts = Trust::count();
        if ($totalTrusts == 0) return 0;
        
        // Assuming trusts are returned after 30 days
        $returnedTrusts = Trust::where('created_at', '<=', now()->subDays(30))->count();
        $returnRate = ($returnedTrusts / $totalTrusts) * 100;
        
        return round($returnRate, 1);
    }

    private function getTrustTrends(): array
    {
        // Get trust trends for last 6 months
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $count = Trust::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
            $trends[$month->format('M Y')] = $count;
        }
        return $trends;
    }

    private function getCriticalStockItemsCount(): int
    {
        // Get items with critical stock (less than 5 units)
        $criticalItems = Receiving::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('SUM(quantity) < 5')
            ->count();
        
        return $criticalItems;
    }

    private function getExpiredItemsCount(): int
    {
        // For now, return 0 as we don't have expiry dates
        // This can be enhanced when expiry tracking is added
        return 0;
    }

    private function getInventoryTurnoverRate(): float
    {
        // Calculate inventory turnover rate
        $totalReceived = Receiving::sum('quantity');
        $totalRequisitioned = Requisition::sum('quantity');
        
        if ($totalReceived == 0) return 0;
        
        $turnoverRate = $totalRequisitioned / $totalReceived;
        return round($turnoverRate, 2);
    }

    private function getStockValuationMetrics(): float
    {
        // Get total stock value
        $itemUnitPrices = $this->getItemUnitPrices();
        return $this->getTotalInventoryValue($itemUnitPrices);
    }

    private function getUserDepartmentStock(): float
    {
        $user = Auth::user();
        if (!$user || !$user->department_id) return 0;
        
        $itemUnitPrices = $this->getItemUnitPrices();
        
        // Calculate stock value for user's department
        $deptReceivings = Receiving::where('department_id', $user->department_id)
            ->selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->get();
        
        $totalValue = 0;
        foreach ($deptReceivings as $receiving) {
            $unitPrice = $itemUnitPrices[$receiving->item_id] ?? 0;
            $totalValue += $receiving->total_quantity * $unitPrice;
        }
        
        return $totalValue;
    }

    private function getItemsNeedingRestock(): int
    {
        // Count items that need restocking (less than 20 units)
        $itemsNeedingRestock = Receiving::selectRaw('item_id, SUM(quantity) as total_quantity')
            ->groupBy('item_id')
            ->havingRaw('SUM(quantity) < 20')
            ->count();
        
        return $itemsNeedingRestock;
    }

    private function getStorageUtilization(): float
    {
        // Calculate storage utilization (simplified)
        $totalItems = Item::count();
        $totalReceivings = Receiving::sum('quantity');
        
        if ($totalItems == 0) return 0;
        
        $utilization = min(100, ($totalReceivings / ($totalItems * 50)) * 100);
        return round($utilization, 1);
    }

    private function getDailyOperationsMetrics(): array
    {
        // Get daily operations metrics
        $today = today();
        $yesterday = today()->subDay();
        
        return [
            'today_receivings' => Receiving::whereDate('created_at', $today)->count(),
            'today_requisitions' => Requisition::whereDate('created_at', $today)->count(),
            'today_trusts' => Trust::whereDate('created_at', $today)->count(),
            'yesterday_receivings' => Receiving::whereDate('created_at', $yesterday)->count(),
            'yesterday_requisitions' => Requisition::whereDate('created_at', $yesterday)->count(),
            'yesterday_trusts' => Trust::whereDate('created_at', $yesterday)->count(),
        ];
    }

    private function getMonthlyExpenses(): array
    {
        // Get monthly expenses for last 6 months
        $expenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = Receiving::whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')
                ->value('total') ?? 0;
            $expenses[$month->format('M Y')] = $amount;
        }
        return $expenses;
    }

    private function getCostAnalysisMetrics(): array
    {
        // Get cost analysis metrics
        $totalReceived = Receiving::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')->value('total') ?? 0;
        $totalRequisitioned = $this->calculateFifoAmount(Requisition::class, $this->getItemUnitPrices());
        $totalTrusted = $this->calculateFifoAmount(Trust::class, $this->getItemUnitPrices());
        
        return [
            'total_cost' => $totalReceived,
            'requisitioned_cost' => $totalRequisitioned,
            'trusted_cost' => $totalTrusted,
            'remaining_value' => max(0, $totalReceived - $totalRequisitioned - $totalTrusted)
        ];
    }

    private function getBudgetVsActualMetrics(): array
    {
        // Simplified budget vs actual metrics
        $actualExpenses = Receiving::selectRaw('COALESCE(SUM(quantity * unit_price), 0) as total')->value('total') ?? 0;
        $estimatedBudget = $actualExpenses * 1.1; // Assume 10% buffer
        
        return [
            'budget' => $estimatedBudget,
            'actual' => $actualExpenses,
            'variance' => $estimatedBudget - $actualExpenses,
            'variance_percentage' => round((($estimatedBudget - $actualExpenses) / $estimatedBudget) * 100, 1)
        ];
    }

    private function getAuditTrailItems(): int
    {
        // Count items that have been modified recently (audit trail)
        $recentDate = now()->subDays(30);
        return Receiving::where('updated_at', '>=', $recentDate)->count() +
               Requisition::where('updated_at', '>=', $recentDate)->count() +
               Trust::where('updated_at', '>=', $recentDate)->count();
    }

    private function getDiscrepancyReports(): int
    {
        // Count potential discrepancies (items with negative remaining stock)
        $discrepancies = 0;
        $items = Receiving::selectRaw('item_id, SUM(quantity) as total_received')
            ->groupBy('item_id')
            ->get();
        
        foreach ($items as $item) {
            $totalRequisitioned = Requisition::where('item_id', $item->item_id)->sum('quantity');
            $totalTrusted = Trust::where('item_id', $item->item_id)->sum('quantity');
            $remaining = $item->total_received - $totalRequisitioned - $totalTrusted;
            
            if ($remaining < 0) {
                $discrepancies++;
            }
        }
        
        return $discrepancies;
    }

    private function getComplianceMetrics(): float
    {
        // Calculate compliance score based on data completeness
        $totalReceivings = Receiving::count();
        $completeReceivings = Receiving::whereNotNull('item_id')
            ->whereNotNull('supplier_id')
            ->whereNotNull('department_id')
            ->where('quantity', '>', 0)
            ->where('unit_price', '>', 0)
            ->count();
        
        if ($totalReceivings == 0) return 0;
        
        $complianceScore = ($completeReceivings / $totalReceivings) * 100;
        return round($complianceScore, 1);
    }

    private function getRiskAssessmentMetrics(): array
    {
        // Risk assessment metrics
        $lowStockItems = $this->getLowStockItemsCount();
        $criticalItems = $this->getCriticalStockItemsCount();
        $discrepancies = $this->getDiscrepancyReports();
        
        $riskScore = ($lowStockItems * 0.3) + ($criticalItems * 0.5) + ($discrepancies * 0.2);
        
        return [
            'risk_score' => round($riskScore, 1),
            'risk_level' => $riskScore > 10 ? 'High' : ($riskScore > 5 ? 'Medium' : 'Low'),
            'low_stock_risk' => $lowStockItems,
            'critical_stock_risk' => $criticalItems,
            'discrepancy_risk' => $discrepancies
        ];
    }

    private function getDepartmentPerformance(): array
    {
        // Department performance metrics
        $user = Auth::user();
        if (!$user || !$user->department_id) return [];
        
        $deptReceivings = Receiving::where('department_id', $user->department_id)->count();
        $deptRequisitions = Requisition::where('department_id', $user->department_id)->count();
        $deptTrusts = Trust::where('department_id', $user->department_id)->count();
        
        return [
            'receivings' => $deptReceivings,
            'requisitions' => $deptRequisitions,
            'trusts' => $deptTrusts,
            'total_operations' => $deptReceivings + $deptRequisitions + $deptTrusts
        ];
    }

    private function getTeamActivityMetrics(): array
    {
        // Team activity metrics for user's department
        $user = Auth::user();
        if (!$user || !$user->department_id) return [];
        
        $recentDate = now()->subDays(7);
        
        $recentReceivings = Receiving::where('department_id', $user->department_id)
            ->where('created_at', '>=', $recentDate)
            ->count();
        $recentRequisitions = Requisition::where('department_id', $user->department_id)
            ->where('created_at', '>=', $recentDate)
            ->count();
        
        return [
            'recent_receivings' => $recentReceivings,
            'recent_requisitions' => $recentRequisitions,
            'activity_score' => round(($recentReceivings + $recentRequisitions) / 7, 1)
        ];
    }

    private function getResourceUtilization(): float
    {
        // Calculate resource utilization for user's department
        $user = Auth::user();
        if (!$user || !$user->department_id) return 0;
        
        $deptReceivings = Receiving::where('department_id', $user->department_id)->sum('quantity');
        $deptRequisitions = Requisition::where('department_id', $user->department_id)->sum('quantity');
        
        if ($deptReceivings == 0) return 0;
        
        $utilization = ($deptRequisitions / $deptReceivings) * 100;
        return round(min(100, $utilization), 1);
    }

    private function getDepartmentEfficiencyMetrics(): array
    {
        // Department efficiency metrics
        $user = Auth::user();
        if (!$user || !$user->department_id) return [];
        
        $deptReceivings = Receiving::where('department_id', $user->department_id)->count();
        $deptRequisitions = Requisition::where('department_id', $user->department_id)->count();
        
        $efficiency = $deptReceivings > 0 ? ($deptRequisitions / $deptReceivings) * 100 : 0;
        
        return [
            'efficiency_score' => round($efficiency, 1),
            'efficiency_level' => $efficiency > 80 ? 'Excellent' : ($efficiency > 60 ? 'Good' : ($efficiency > 40 ? 'Average' : 'Poor')),
            'receivings_count' => $deptReceivings,
            'requisitions_count' => $deptRequisitions
        ];
    }
}