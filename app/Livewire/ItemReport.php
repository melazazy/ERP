<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Models\Department;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ItemReport extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedDepartment = '';
    public $departments = [];
    public $items = [];
    public $totalOpeningQuantity = 0;
    public $totalOpeningAmount = 0;
    public $totalInQuantity = 0;
    public $totalInAmount = 0;
    public $totalAvailableQuantity = 0;
    public $totalAvailableAmount = 0;
    public $totalOutQuantity = 0;
    public $totalOutAmount = 0;
    public $totalBalanceQuantity = 0;
    public $totalBalanceAmount = 0;

    public function mount()
    {
        $this->departments = Department::orderBy('name')->get();
    }

    public function loadItems()
    {
        if (!$this->selectedDepartment) {
            $this->items = [];
            $this->resetTotals();
            return;
        }

        $query = Item::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedDepartment !== 'all', function ($query) {
                $query->whereHas('requisitions', function ($req) {
                    $req->where('department_id', $this->selectedDepartment);
                });
            });

        $items = $query->get();

        $this->items = $items->map(function ($item) {
            $balances = $this->calculateItemBalances($item);
            
            return [
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
        })->toArray();

        $this->calculateTotals();
    }

    private function resetTotals()
    {
        $this->totalOpeningQuantity = 0;
        $this->totalOpeningAmount = 0;
        $this->totalInQuantity = 0;
        $this->totalInAmount = 0;
        $this->totalAvailableQuantity = 0;
        $this->totalAvailableAmount = 0;
        $this->totalOutQuantity = 0;
        $this->totalOutAmount = 0;
        $this->totalBalanceQuantity = 0;
        $this->totalBalanceAmount = 0;
    }

    public function updatedSelectedDepartment()
    {
        $this->loadItems();
    }

    public function updatedSearch()
    {
        $this->loadItems();
    }

    private function calculateItemBalances($item)
    {
        $startOfMonth = now()->startOfMonth();
        
        // Calculate opening balance (all transactions before current month)
        $openingReceivings = $item->receivings()
            ->where('received_at', '<', $startOfMonth)
            ->get();
            
        $openingRequisitions = $item->requisitions()
            ->where('requested_date', '<', $startOfMonth)
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department_id', $this->selectedDepartment);
            })
            ->get();
            
        $openingTrusts = $item->trusts()
            ->where('requested_date', '<', $startOfMonth)
            ->get();

        // Calculate current month transactions
        $currentReceivings = $item->receivings()
            ->whereMonth('received_at', now()->month)
            ->get();
            
        $currentRequisitions = $item->requisitions()
            ->whereMonth('requested_date', now()->month)
            ->when($this->selectedDepartment, function ($query) {
                $query->where('department_id', $this->selectedDepartment);
            })
            ->get();
            
        $currentTrusts = $item->trusts()
            ->whereMonth('requested_date', now()->month)
            ->get();

        // Opening Balance Calculations
        $openingInQuantity = $openingReceivings->sum('quantity');
        $openingOutQuantity = $openingRequisitions->sum('quantity') + $openingTrusts->sum('quantity');
        $openingQuantity = $openingInQuantity - $openingOutQuantity;
        
        $openingInValue = $openingReceivings->sum(function ($receiving) {
            return $receiving->quantity * $receiving->unit_price;
        });
        $openingUnitCost = $openingInQuantity > 0 ? $openingInValue / $openingInQuantity : 0;
        $openingAmount = $openingQuantity * $openingUnitCost;

        // Current Month Calculations
        $inQuantity = $currentReceivings->sum('quantity');
        $inAmount = $currentReceivings->sum(function ($receiving) {
            return $receiving->quantity * $receiving->unit_price;
        });

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
            'opening_quantity' => $openingQuantity,
            'opening_unit_cost' => $openingUnitCost,
            'opening_amount' => $openingAmount,
            'in_quantity' => $inQuantity,
            'in_amount' => $inAmount,
            'total_available_quantity' => $totalAvailableQuantity,
            'total_available_amount' => $totalAvailableAmount,
            'out_quantity' => $outQuantity,
            'out_amount' => $outAmount,
            'balance_quantity' => $balanceQuantity,
            'balance_amount' => $balanceAmount,
            'unit_cost' => $unitCost
        ];
    }

    private function calculateTotals()
    {
        $this->totalOpeningQuantity = collect($this->items)->sum('opening_quantity');
        $this->totalOpeningAmount = collect($this->items)->sum('opening_amount');
        $this->totalInQuantity = collect($this->items)->sum('in_quantity');
        $this->totalInAmount = collect($this->items)->sum('in_amount');
        $this->totalAvailableQuantity = collect($this->items)->sum('total_available_quantity');
        $this->totalAvailableAmount = collect($this->items)->sum('total_available_amount');
        $this->totalOutQuantity = collect($this->items)->sum('out_quantity');
        $this->totalOutAmount = collect($this->items)->sum('out_amount');
        $this->totalBalanceQuantity = collect($this->items)->sum('balance_quantity');
        $this->totalBalanceAmount = collect($this->items)->sum('balance_amount');
    }

    public function exportToExcel()
    {
        $fileName = 'item_report_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new class($this->items, $this->totalOpeningQuantity, $this->totalOpeningAmount,
            $this->totalInQuantity, $this->totalInAmount, $this->totalAvailableQuantity, $this->totalAvailableAmount,
            $this->totalOutQuantity, $this->totalOutAmount, $this->totalBalanceQuantity, $this->totalBalanceAmount) implements 
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\WithColumnWidths
        {
            private $items;
            private $totals;

            public function __construct($items, ...$totals)
            {
                $this->items = $items;
                $this->totals = $totals;
            }

            public function collection()
            {
                $data = collect($this->items)->map(function ($item) {
                    return [
                        $item['code'],
                        $item['name'],
                        $item['unit'],
                        $item['opening_quantity'],
                        $item['opening_unit_cost'],
                        $item['opening_amount'],
                        $item['in_quantity'],
                        $item['in_amount'],
                        $item['total_available_quantity'],
                        $item['total_available_amount'],
                        $item['out_quantity'],
                        $item['out_amount'],
                        $item['balance_quantity'],
                        $item['balance_amount'],
                        $item['act'],
                        $item['diff']
                    ];
                });

                // Add totals row
                $data->push([
                    'Totals', '', '',
                    $this->totals[0], // opening quantity
                    '-',
                    $this->totals[1], // opening amount
                    $this->totals[2], // in quantity
                    $this->totals[3], // in amount
                    $this->totals[4], // available quantity
                    $this->totals[5], // available amount
                    $this->totals[6], // out quantity
                    $this->totals[7], // out amount
                    $this->totals[8], // balance quantity
                    $this->totals[9], // balance amount
                    '-',
                    '-'
                ]);

                return $data;
            }

            public function headings(): array
            {
                return [
                    [
                        'Code',
                        'Item',
                        'Unit',
                        'Opening Balance',
                        '',
                        '',
                        'IN',
                        '',
                        'Total Available',
                        '',
                        'OUT',
                        '',
                        'Balance',
                        '',
                        'Additional',
                        ''
                    ],
                    [
                        '',
                        '',
                        '',
                        'Quant',
                        'Unit Cost',
                        'Amount',
                        'Quant',
                        'Amount',
                        'Quant',
                        'Amount',
                        'Quant',
                        'Amount',
                        'Quant',
                        'Amount',
                        'Act',
                        'Diff'
                    ],
                ];
            }

            public function styles($sheet)
            {
                // Style for headers
                $sheet->getStyle('A1:P2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);

                // Merge cells for first row headers
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:F1'); // Opening Balance
                $sheet->mergeCells('G1:H1'); // IN
                $sheet->mergeCells('I1:J1'); // Total Available
                $sheet->mergeCells('K1:L1'); // OUT
                $sheet->mergeCells('M1:N1'); // Balance
                $sheet->mergeCells('O1:P1'); // Additional

                // Color coding for sections
                $sheet->getStyle('D1:F2')->getFill()->setFillType('solid')->getStartColor()->setRGB('87CEEB'); // Opening Balance
                $sheet->getStyle('G1:H2')->getFill()->setFillType('solid')->getStartColor()->setRGB('98FB98'); // IN
                $sheet->getStyle('I1:J2')->getFill()->setFillType('solid')->getStartColor()->setRGB('F0E68C'); // Total Available
                $sheet->getStyle('K1:L2')->getFill()->setFillType('solid')->getStartColor()->setRGB('FFB6C1'); // OUT
                $sheet->getStyle('M1:N2')->getFill()->setFillType('solid')->getStartColor()->setRGB('98FB98'); // Balance
                $sheet->getStyle('O1:P2')->getFill()->setFillType('solid')->getStartColor()->setRGB('D3D3D3'); // Additional

                // Style for data cells
                $lastRow = count($this->items) + 3;
                $sheet->getStyle('A3:P'.$lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'right'],
                ]);
                $sheet->getStyle('A3:C'.$lastRow)->applyFromArray([
                    'alignment' => ['horizontal' => 'left'],
                ]);

                // Style for totals row
                $sheet->getStyle('A'.$lastRow.':P'.$lastRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'borders' => ['top' => ['borderStyle' => 'thin']],
                ]);

                return $sheet;
            }

            public function columnWidths(): array
            {
                return [
                    'A' => 12, // Code
                    'B' => 30, // Item
                    'C' => 10, // Unit
                    'D' => 12, // Opening Quant
                    'E' => 12, // Opening Unit Cost
                    'F' => 15, // Opening Amount
                    'G' => 12, // IN Quant
                    'H' => 15, // IN Amount
                    'I' => 12, // Available Quant
                    'J' => 15, // Available Amount
                    'K' => 12, // OUT Quant
                    'L' => 15, // OUT Amount
                    'M' => 12, // Balance Quant
                    'N' => 15, // Balance Amount
                    'O' => 10, // Act
                    'P' => 10, // Diff
                ];
            }
        }, $fileName);
    }
    
    public function render()
    {
        return view('livewire.item-report')->layout('layouts.app');
    }
}