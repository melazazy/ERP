<?php

namespace App\Livewire;

use App\Models\Item;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ItemMonitor extends Component
{
    use WithPagination;
    use ItemSearchTrait;

    public $requisitions;
    public $trusts;
    public $receivings;
    public $selectedItem;
    public $movements = [];
    public $itemSearch = '';
    public $items = [];
    public $totalIn = 0;
    public $totalOut = 0;
    public $balance = 0;
    public $totalInPrice = 0;
    public $totalOutPrice = 0;
    public $balancePrice = 0;
    public $selectedItemCode = '';
    public $perPage = 10;

    public function mount()
    {
        $this->items = [];
    }

    public function fetchReports()
    {
        $this->requisitions = Requisition::with(['item', 'department', 'requester'])->get();
        $this->trusts = Trust::with(['item', 'department', 'requester'])->get();
        $this->receivings = Receiving::with(['item', 'supplier', 'department'])->get();
    }

    public function updatedItemSearch()
    {
        $this->items = $this->searchItems($this->itemSearch, $this->perPage);
    }

    public function selectItem($itemId)
    {
        $item = Item::find($itemId);
        $this->selectedItem = $itemId;
        $this->selectedItemCode = $item->code;
        $this->itemSearch = $item->name;
        $this->items = [];
        $this->getMovements();
    }

    public function getMovements()
    {
        if (!$this->selectedItem) {
            return;
        }

        $item = Item::with([
            'receivings.supplier',
            'receivings.department',
            'requisitions.department',
            'trusts.department',
        ])->find($this->selectedItem);

        if (!$item) {
            return;
        }

        $receivings = $item->receivings->map(function ($receiving) {
            return [
                'date' => $receiving->received_at,
                'document_number' => $receiving->receiving_number,
                'description' => ($receiving->supplier ? $receiving->supplier->name : '') . ' → ' . ($receiving->department ? $receiving->department->name : ''),
                'in' => $receiving->quantity,
                'out' => null,
                'balance' => null,
                'in_price' => $receiving->unit_price,
                'out_price' => null,
                'balance_price' => null,
                'type' => 'in',
                'transaction_type' => 'receiving'
            ];
        });

        $requisitions = $item->requisitions->map(function ($requisition) {
            return [
                'date' => $requisition->requested_date,
                'document_number' => $requisition->requisition_number,
                'description' => $requisition->department ? $requisition->department->name : '',
                'in' => null,
                'out' => $requisition->quantity,
                'balance' => null,
                'in_price' => null,
                'out_price' => null, // Will be set during movement calculation
                'balance_price' => null,
                'type' => 'out',
                'transaction_type' => 'requisition'
            ];
        });

        $trusts = $item->trusts->map(function ($trust) {
            return [
                'date' => $trust->requested_date,
                'document_number' => $trust->trust_number,
                'description' => $trust->department ? $trust->department->name : '',
                'in' => null,
                'out' => $trust->quantity,
                'balance' => null,
                'in_price' => null,
                'out_price' => null, // Will be set during movement calculation
                'balance_price' => null,
                'type' => 'out',
                'transaction_type' => 'trust'
            ];
        });

        $allMovements = $receivings
            ->concat($requisitions)
            ->concat($trusts)
            ->sortBy('date')
            ->values()
            ->toArray();

        // Calculate running balance and prices
        $balance = 0;
        $balancePrice = 0;
        $totalValue = 0;

        foreach ($allMovements as &$movement) {
            if ($movement['type'] === 'in') {
                // For incoming items
                $newQuantity = $movement['in'];
                $newPrice = $movement['in_price'];
                
                // Calculate new weighted average
                $totalValue = ($balance * $balancePrice) + ($newQuantity * $newPrice);
                $balance += $newQuantity;
                $balancePrice = $balance > 0 ? $totalValue / $balance : 0;
            } else {
                // For outgoing items
                $outQuantity = $movement['out'];
                // Set the out_price to current weighted average
                $movement['out_price'] = $balancePrice;
                $balance -= $outQuantity;
                // Total value reduces proportionally
                $totalValue = $balance * $balancePrice;
            }
            
            $movement['balance'] = $balance;
            $movement['balance_price'] = $balancePrice;
        }

        $this->movements = $allMovements;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->totalIn = collect($this->movements)->sum('in') ?? 0;
        $this->totalOut = collect($this->movements)->sum('out') ?? 0;
        $this->balance = $this->totalIn - $this->totalOut;
        $this->totalInPrice = collect($this->movements)->sum('in_price') ?? 0;
        $this->totalOutPrice = collect($this->movements)->sum('out_price') ?? 0;
        $this->balancePrice = $this->totalInPrice - $this->totalOutPrice;
    }

    public function exportToExcel()
    {
        $movements = collect($this->movements)->map(function ($movement) {
            return [
                'Date' => date('d/m/Y', strtotime($movement['date'])),
                'ID' => $movement['transaction_id'],
                'Document No.' => $movement['document_number'],
                'Description' => $movement['description'],
                'Quantity In' => $movement['in'] ?? '-',
                'Quantity Out' => $movement['out'] ?? '-',
                'Quantity Balance' => $movement['balance'],
                'Price In' => $movement['in_price'] ?? '-',
                'Price Out' => $movement['out_price'] ?? '-',
                'Price Balance' => $movement['balance_price']
            ];
        });

        $fileName = 'item_movements_' . $this->selectedItemCode . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new class($movements, $this->itemSearch, $this->selectedItemCode, $this->totalIn, $this->totalOut, $this->balance, $this->totalInPrice, $this->totalOutPrice, $this->balancePrice) implements
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\WithTitle
        {
            private $movements;
            private $itemName;
            private $itemCode;
            private $totalIn;
            private $totalOut;
            private $balance;
            private $totalInPrice;
            private $totalOutPrice;
            private $balancePrice;

            public function __construct($movements, $itemName, $itemCode, $totalIn, $totalOut, $balance, $totalInPrice, $totalOutPrice, $balancePrice)
            {
                $this->movements = $movements;
                $this->itemName = $itemName;
                $this->itemCode = $itemCode;
                $this->totalIn = $totalIn;
                $this->totalOut = $totalOut;
                $this->balance = $balance;
                $this->totalInPrice = $totalInPrice;
                $this->totalOutPrice = $totalOutPrice;
                $this->balancePrice = $balancePrice;
            }

            public function collection()
            {
                return $this->movements;
            }

            public function headings(): array
            {
                return [
                    ['Item Card: ' . $this->itemName],
                    ['Item Code: ' . $this->itemCode],
                    ['', '', '', '', 'Quantities', '', '', 'Prices', '', ''],
                    ['Date', 'ID', 'Document No.', 'Description', 'In', 'Out', 'Balance', 'In', 'Out', 'Balance'],
                ];
            }

            public function styles($sheet)
            {
                // Merge cells for title and item code
                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('E3:G3');
                $sheet->mergeCells('H3:J3');

                // Title styling
                $sheet->getStyle('A1:J2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Headers styling
                $sheet->getStyle('A3:J4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF4B5563'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Data styling
                $dataRange = 'A5:J' . ($this->movements->count() + 4);
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // First set all text to black
                $sheet->getStyle('A5:J' . ($this->movements->count() + 4))->getFont()->getColor()->setARGB('FF000000');

                // Style "In" quantities and prices in red
                $lastRow = $this->movements->count() + 4;
                for ($row = 5; $row <= $lastRow; $row++) {
                    $inQuantity = $sheet->getCell('E' . $row)->getValue();
                    $inPrice = $sheet->getCell('H' . $row)->getValue();
                    
                    if ($inQuantity !== '-') {
                        $sheet->getStyle('E' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                    }
                    if ($inPrice !== '-') {
                        $sheet->getStyle('H' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                    }
                }

                // Add totals row
                $lastDataRow = $this->movements->count() + 5;
                $sheet->setCellValue("A{$lastDataRow}", '');
                $sheet->setCellValue("B{$lastDataRow}", '');
                $sheet->setCellValue("C{$lastDataRow}", '');
                $sheet->setCellValue("D{$lastDataRow}", 'Total');
                $sheet->setCellValue("E{$lastDataRow}", $this->totalIn);
                $sheet->setCellValue("F{$lastDataRow}", $this->totalOut);
                $sheet->setCellValue("G{$lastDataRow}", $this->balance);
                $sheet->setCellValue("H{$lastDataRow}", $this->totalInPrice);
                $sheet->setCellValue("I{$lastDataRow}", $this->totalOutPrice);
                $sheet->setCellValue("J{$lastDataRow}", $this->balancePrice);

                // Style the totals row
                $sheet->getStyle("A{$lastDataRow}:J{$lastDataRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF3F4F6'],
                    ],
                ]);

                // Make "In" total red in the totals row
                $sheet->getStyle("E{$lastDataRow}")->getFont()->getColor()->setARGB('FFFF0000');

                // Auto-size columns
                foreach (range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                return $sheet;
            }

            public function title(): string
            {
                return 'Item Movements';
            }
        }, $fileName);
    }

    public function render()
    {
        return view('livewire.item-monitor')->layout('layouts.app');
    }
}
