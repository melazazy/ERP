<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemReportExport;
use Illuminate\Support\Facades\Cache;

class ItemReport extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $departments = [];
    public $allowMonthChange = false;
    public $selectedMonth;
    protected $items = []; // Initialize items as protected property

    public function mount()
    {
        $this->departments = Department::orderBy('name')->get();
        $this->selectedMonth = now()->month;
        $this->items = []; // Initialize items in mount
    }

    public function updatedAllowMonthChange()
    {
        if (!$this->allowMonthChange) {
            $this->selectedMonth = now()->month;
        }
        // No need to call loadItems() - computed property handles this
    }

    public function updatedSelectedMonth($value)
    {
        $this->selectedMonth = $value;
        // No need to call loadItems() - computed property handles this
    }

    public function updatedSelectedDepartment()
    {
        $this->resetPage(); // Reset pagination when department changes
        // No need to call loadItems() - computed property handles this
    }

    public function updatedSearch()
    {
        $this->resetPage(); // Reset pagination when search changes
        // No need to call loadItems() - computed property handles this
    }

    // Computed property for items - this prevents storing large arrays in component state
    public function getItemsProperty()
    {
        if (!$this->selectedDepartment) {
            return [];
        }

        try {
            // Create cache key for better performance
            $cacheKey = sprintf(
                'item_report_%s_%s_%s_%s',
                $this->selectedDepartment,
                $this->selectedMonth,
                md5($this->search),
                now()->format('Y-m-d') // Add date to cache key to expire daily
            );

            return Cache::remember($cacheKey, now()->endOfDay(), function () {
                $query = Item::query()
                    ->when($this->search, function ($query) {
                        $query->where(function ($q) {
                            $q->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('code', 'like', '%' . $this->search . '%');
                        });
                    });

                // Only filter by department if it's not 'all' and the department is selected
                if ($this->selectedDepartment !== 'all' && $this->selectedDepartment !== '') {
                    $query->whereHas('requisitions', function ($req) {
                        $req->where('department_id', $this->selectedDepartment);
                    });
                }

                $items = $query->get();

                \Log::info("Found " . $items->count() . " items for department " . $this->selectedDepartment);

                return $items->map(function ($item) {
                    try {
                        $balances = $this->calculateItemBalances($item);
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'code' => $item->code,
                            'unit' => $item->unit,
                            'opening_quantity' => $balances['opening_quantity'],
                            'opening_unit_cost' => $balances['opening_unit_cost'],
                            'opening_amount' => $balances['opening_amount'],
                            'in_quantity' => $balances['in_quantity'],
                            'in_amount' => $balances['in_amount'],
                            'total_available_quantity' => $balances['total_available_quantity'],
                            'total_available_amount' => $balances['total_available_amount'],
                            'out_quantity' => $balances['out_quantity'],
                            'out_amount' => $balances['out_amount'],
                            'balance_quantity' => $balances['balance_quantity'],
                            'balance_amount' => $balances['balance_amount'],
                            'unit_cost' => $balances['unit_cost'],
                            'act' => 0,
                            'diff' => 0
                        ];
                    } catch (\Exception $e) {
                        \Log::error("Error calculating balances for item {$item->id}: " . $e->getMessage());
                        return null;
                    }
                })->filter()->toArray();
            });
        } catch (\Exception $e) {
            \Log::error("Error in getItemsProperty: " . $e->getMessage());
            return [];
        }
    }

    // Computed properties for totals
    public function getTotalOpeningQuantityProperty()
    {
        return collect($this->items)->sum('opening_quantity');
    }

    public function getTotalOpeningAmountProperty()
    {
        return collect($this->items)->sum('opening_amount');
    }

    public function getTotalInQuantityProperty()
    {
        return collect($this->items)->sum('in_quantity');
    }

    public function getTotalInAmountProperty()
    {
        return collect($this->items)->sum('in_amount');
    }

    public function getTotalAvailableQuantityProperty()
    {
        return collect($this->items)->sum('total_available_quantity');
    }

    public function getTotalAvailableAmountProperty()
    {
        return collect($this->items)->sum('total_available_amount');
    }

    public function getTotalOutQuantityProperty()
    {
        return collect($this->items)->sum('out_quantity');
    }

    public function getTotalOutAmountProperty()
    {
        return collect($this->items)->sum('out_amount');
    }

    public function getTotalBalanceQuantityProperty()
    {
        return collect($this->items)->sum('balance_quantity');
    }

    public function getTotalBalanceAmountProperty()
    {
        return collect($this->items)->sum('balance_amount');
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
                ->get();

            $openingRequisitions = $item
                ->requisitions()
                ->where('requested_date', '<=', $endOfPreviousMonth)
                ->when($this->selectedDepartment && $this->selectedDepartment !== 'all', function ($query) {
                    $query->where('department_id', $this->selectedDepartment);
                })
                ->get();

            $openingTrusts = $item
                ->trusts()
                ->where('requested_date', '<=', $endOfPreviousMonth)
                ->get();

            // Calculate totals for this item
            $totalInQuantity = $openingReceivings->sum('quantity');
            $totalInValue = $openingReceivings->sum(function ($receiving) {
                return $receiving->quantity * ($receiving->unit_price ?? 0);
            });

            $totalOutQuantity = $openingRequisitions->sum('quantity') + $openingTrusts->sum('quantity');
            $openingQuantity = $totalInQuantity - $totalOutQuantity;

            // Calculate weighted average unit cost with division by zero protection
            $openingUnitCost = $totalInQuantity > 0 ? $totalInValue / $totalInQuantity : 0;

            // Calculate opening amount using actual transactions
            $transactions = $openingReceivings->merge($openingRequisitions)->merge($openingTrusts);
            $transactions = $transactions->sortBy(function ($transaction) {
                return $transaction->received_at ?? $transaction->requested_date;
            })->values();

            $currentQuantity = 0;
            $currentValue = 0;

            foreach ($transactions as $transaction) {
                if ($transaction instanceof \App\Models\Receiving) {
                    $currentQuantity += $transaction->quantity;
                    $currentValue += $transaction->quantity * ($transaction->unit_price ?? 0);
                } else {
                    $currentQuantity -= $transaction->quantity;
                }

                if ($currentQuantity <= 0) {
                    $currentValue = 0;
                }
            }

            $openingAmount = $currentValue;
            if ($openingQuantity < 0) {
                $openingUnitCost = $currentQuantity != 0 ? $currentValue / $currentQuantity : 0;
            }

            // Current month transactions (only for selected month)
            $currentReceivings = $item
                ->receivings()
                ->whereBetween('received_at', [$startOfMonth, $endOfMonth])
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
                ->get();

            $currentTrusts = $item
                ->trusts()
                ->whereBetween('requested_date', [$startOfMonth, $endOfMonth])
                ->get();

            $outQuantity = $currentRequisitions->sum('quantity') + $currentTrusts->sum('quantity');
            $outAmount = $outQuantity * $openingUnitCost;

            // Total Available
            $totalAvailableQuantity = $openingQuantity + $inQuantity;
            $totalAvailableAmount = $openingAmount + $inAmount;

            // Final Balance
            $balanceQuantity = $totalAvailableQuantity - $outQuantity;
            $balanceAmount = $totalAvailableAmount - $outAmount;
            $unitCost = $balanceQuantity > 0 ? $balanceAmount / $balanceQuantity : $openingUnitCost;

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
            \Log::error("Error calculating balances for item {$item->id}: " . $e->getMessage());
            throw $e;
        }
    }

    public function exportToExcel()
    {
        try {
            // Get fresh data for export
            $items = $this->items;
            
            if (empty($items)) {
                $this->dispatch('error', message: __('messages.no_data_to_export'));
                return null;
            }

            return Excel::download(
                new ItemReportExport($items, $this->selectedMonth, $this->selectedDepartment, $this->search),
                'item-report-' . now()->format('Y-m-d') . '.xlsx'
            );
        } catch (\Exception $e) {
            $this->dispatch('error', message: __('messages.export_error') . $e->getMessage());
            return null;
        }
    }

    // Method to clear cache when needed
    public function clearCache()
    {
        $cacheKey = sprintf(
            'item_report_%s_%s_%s',
            $this->selectedDepartment,
            $this->selectedMonth,
            md5($this->search)
        );
        
        Cache::forget($cacheKey);
        $this->dispatch('success', message: 'Cache cleared successfully');
    }

    public function render()
    {
        return view('livewire.item-report')->layout('layouts.app');
    }
}