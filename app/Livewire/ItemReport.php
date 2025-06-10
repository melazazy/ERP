<?php


namespace App\Livewire;

use App\Models\Department;
use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemReportExport;
use Illuminate\Support\Facades\Cache;


// Solution 1: Don't store large datasets in component properties
class ItemReport extends Component
{
    use WithPagination;
    // Remove $items from component properties to prevent serialization
    // public $items = []; // REMOVE THIS LINE
    
    public $departments;
    public $selectedDepartment;
    public $selectedMonth;
    public $search = '';
    
    // Add property type casting
    protected $casts = [
        'selectedMonth' => 'integer'
    ];

    public function mount()
    {
        $this->departments = Department::orderBy('name')->get();
        $this->selectedMonth = (int)now()->month;
    }

    public function updatedSelectedMonth($value)
    {
        try {
            $this->selectedMonth = (int)$value;
            $this->resetPage();
            $this->clearCache();
            // Don't call loadItems() here - let the view handle it
        } catch (\Exception $e) {
            $this->selectedMonth = (int)now()->month;
        }
    }

    public function updatedSelectedDepartment($value = null)
    {
        $this->resetPage();
        $this->clearCache();
        // Don't call loadItems() here - let the view handle it
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->clearCache();
        // Don't call loadItems() here - let the view handle it
    }

    // Make this method return data instead of storing it
    public function getItemsProperty()
    {
        if (!$this->selectedDepartment) {
            return collect([]);
        }

        try {
            // Build query
            $query = Item::query();

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->selectedDepartment !== 'all') {
                $query->whereHas('requisitions', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }

            // Always use pagination for display
            $items = $query->paginate(50);

            // Map items to array with balances
            $mappedItems = $items->getCollection()->map(function ($item) {
                try {
                    $balances = $this->calculateItemBalances($item);
                    return array_merge(
                        [
                            'id' => $item->id,
                            'name' => $item->name,
                            'code' => $item->code,
                            'unit' => $item->unit,
                        ],
                        $balances
                    );
                } catch (\Exception $e) {
                    return null;
                }
            })->filter()->values();

            // Replace the collection in the paginator
            $items->setCollection($mappedItems);
            
            return $items;

        } catch (\Exception $e) {
            return collect([]);
        }
    }

    // Computed properties for totals - now work with paginated data
    public function getTotalOpeningQuantityProperty()
    {
        return $this->items->sum('opening_quantity');
    }

    public function getTotalOpeningAmountProperty()
    {
        return $this->items->sum('opening_amount');
    }

    public function getTotalInQuantityProperty()
    {
        return $this->items->sum('in_quantity');
    }

    public function getTotalInAmountProperty()
    {
        return $this->items->sum('in_amount');
    }

    public function getTotalAvailableQuantityProperty()
    {
        return $this->items->sum('total_available_quantity');
    }

    public function getTotalAvailableAmountProperty()
    {
        return $this->items->sum('total_available_amount');
    }

    public function getTotalOutQuantityProperty()
    {
        return $this->items->sum('out_quantity');
    }

    public function getTotalOutAmountProperty()
    {
        return $this->items->sum('out_amount');
    }

    public function getTotalBalanceQuantityProperty()
    {
        return $this->items->sum('balance_quantity');
    }

    public function getTotalBalanceAmountProperty()
    {
        return $this->items->sum('balance_amount');
    }

    private function calculateItemBalances($item)
    {
        try {
            // Calculate dates for the selected month
            $selectedMonth = (int)$this->selectedMonth;
            $startOfMonth = now()->startOfYear()->month($selectedMonth)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
            $endOfPreviousMonth = $startOfMonth->copy()->subDay();

            // Calculate opening balance (all transactions up to end of previous month)
            $openingReceivings = $item
                ->receivings()
                ->where('received_at', '<=', $endOfPreviousMonth)
                ->orderBy('received_at')
                ->get();

            $openingRequisitions = $item
                ->requisitions()
                ->where('requested_date', '<=', $endOfPreviousMonth)
                ->when($this->selectedDepartment && $this->selectedDepartment !== 'all', function ($query) {
                    $query->where('department_id', $this->selectedDepartment);
                })
                ->orderBy('requested_date')
                ->get();

            $openingTrusts = $item
                ->trusts()
                ->where('requested_date', '<=', $endOfPreviousMonth)
                ->orderBy('requested_date')
                ->get();

            // Calculate totals for this item
            $totalInQuantity = $openingReceivings->sum('quantity');
            $totalInValue = $openingReceivings->sum(function ($receiving) {
                return $receiving->quantity * ($receiving->unit_price ?? 0);
            });

            $totalOutQuantity = $openingRequisitions->sum('quantity') + $openingTrusts->sum('quantity');
            $openingQuantity = $totalInQuantity - $totalOutQuantity;

            // Reset values if opening quantity is zero or negative
            if ($openingQuantity <= 0) {
                $openingQuantity = 0;
                $openingUnitCost = 0;
                $openingAmount = 0;
            } else {
                // Calculate weighted average unit cost only if there are incoming items
                $openingUnitCost = $totalInQuantity > 0 ? $totalInValue / $totalInQuantity : 0;
                $openingAmount = $openingQuantity * $openingUnitCost;
            }

            // Current month transactions (only for selected month)
            $currentReceivings = $item
                ->receivings()
                ->whereBetween('received_at', [$startOfMonth, $endOfMonth])
                ->orderBy('received_at')
                ->get();

            // Calculate in_quantity and in_amount for the selected month
            $inQuantity = $currentReceivings->sum('quantity');
            $inAmount = $currentReceivings->sum(function ($receiving) {
                return $receiving->quantity * ($receiving->unit_price ?? 0);
            });

            // Calculate out_quantity and out_amount for the selected month
            $currentRequisitions = $item
                ->requisitions()
                ->whereBetween('requested_date', [$startOfMonth, $endOfMonth])
                ->when($this->selectedDepartment && $this->selectedDepartment !== 'all', function ($query) {
                    $query->where('department_id', $this->selectedDepartment);
                })
                ->orderBy('requested_date')
                ->get();

            $currentTrusts = $item
                ->trusts()
                ->whereBetween('requested_date', [$startOfMonth, $endOfMonth])
                ->orderBy('requested_date')
                ->get();

            $outQuantity = $currentRequisitions->sum('quantity') + $currentTrusts->sum('quantity');

            // Calculate total available
            $totalAvailableQuantity = $openingQuantity + $inQuantity;
            
            // Calculate new weighted average cost if there are new receivings
            if ($inQuantity > 0) {
                $totalAvailableAmount = ($openingQuantity * $openingUnitCost) + $inAmount;
                $weightedAverageCost = $totalAvailableQuantity > 0 ? $totalAvailableAmount / $totalAvailableQuantity : 0;
            } else {
                $totalAvailableAmount = $totalAvailableQuantity * $openingUnitCost;
                $weightedAverageCost = $openingUnitCost;
            }

            // Calculate out amount using weighted average cost
            $outAmount = $outQuantity * $weightedAverageCost;

            // Calculate final balance
            $balanceQuantity = $totalAvailableQuantity - $outQuantity;
            
            // Reset values if balance quantity is zero or negative
            if ($balanceQuantity <= 0) {
                $balanceQuantity = 0;
                $balanceAmount = 0;
                $unitCost = 0;
            } else {
                $balanceAmount = $balanceQuantity * $weightedAverageCost;
                $unitCost = $weightedAverageCost;
            }

            return [
                'opening_quantity' => round($openingQuantity, 2),
                'opening_unit_cost' => round($openingUnitCost, 2),
                'opening_amount' => round($openingAmount, 2),
                'in_quantity' => round($inQuantity, 2),
                'in_amount' => round($inAmount, 2),
                'total_available_quantity' => round($totalAvailableQuantity, 2),
                'total_available_amount' => round($totalAvailableAmount, 2),
                'out_quantity' => round($outQuantity, 2),
                'out_amount' => round($outAmount, 2),
                'balance_quantity' => round($balanceQuantity, 2),
                'balance_amount' => round($balanceAmount, 2),
                'unit_cost' => round($unitCost, 2)
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    // Separate method to get all items for export (not stored in component)
    private function getAllItemsForExport()
    {
        if (!$this->selectedDepartment) {
            return collect([]);
        }

        try {
            // Build query
            $query = Item::query();

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            }

            if ($this->selectedDepartment !== 'all') {
                $query->whereHas('requisitions', function ($q) {
                    $q->where('department_id', $this->selectedDepartment);
                });
            }

            // Get all items for export
            $items = $query->get();

            // Map items to array with balances
            return $items->map(function ($item) {
                try {
                    $balances = $this->calculateItemBalances($item);
                    return array_merge(
                        [
                            'id' => $item->id,
                            'name' => $item->name,
                            'code' => $item->code,
                            'unit' => $item->unit,
                        ],
                        $balances
                    );
                } catch (\Exception $e) {
                    return null;
                }
            })->filter()->values()->toArray();

        } catch (\Exception $e) {
            return collect([]);
        }
    }

    public function exportToExcel()
    {
        try {
            // Get all items for export without storing in component
            $exportItems = $this->getAllItemsForExport();

            if (empty($exportItems)) {
                $this->dispatch('error', message: __('messages.no_data_to_export'));
                return null;
            }

            // Get month name
            $monthName = date('F', mktime(0, 0, 0, $this->selectedMonth, 1));
            $fileName = "item-report-{$monthName}" . '.xlsx';

            return Excel::download(
                new ItemReportExport($exportItems, $this->selectedMonth, $this->selectedDepartment, $this->search),
                $fileName
            );

        } catch (\Exception $e) {
            $this->dispatch('error', message: __('messages.export_error'));
            return null;
        }
    }

    // Method to clear cache when needed
    public function clearCache()
    {
        $cacheKey = "item_report_{$this->selectedDepartment}_{$this->selectedMonth}_{$this->search}";
        Cache::forget($cacheKey);
    }

    public function render()
    {
        return view('livewire.item-report')->layout('layouts.app');
    }
}