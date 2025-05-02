<?php

namespace App\Livewire;

use App\Livewire\ItemSearchTrait;
use App\Models\Item;
use App\Models\Receiving;
use App\Models\Requisition;
use App\Models\Trust;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ItemCard extends Component
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
    public $selectedItemCode = '';
    protected $paginationTheme = 'tailwind';
    public $perPage = 50;  // Items per page


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
        $allMovements = collect();

        // Process receivings in chunks
        Item::with(['receivings' => function($q) {
            $q->orderBy('received_at')->with('supplier');
        }])->where('id', $this->selectedItem)
        ->chunk(1000, function($items) use (&$allMovements) {
            foreach($items as $item) {
                $receivings = $item->receivings->map(function ($receiving) {
                    return [
                        'date' => $receiving->received_at,
                        'document_number' => $receiving->receiving_number,
                        'type' => 'in',
                        'in_quantity' => $receiving->quantity,
                        'out_quantity' => null,
                        'description' => $receiving->supplier->name
                    ];
                });
                $allMovements = $allMovements->concat($receivings);
            }
        });

        // Process requisitions in chunks
        Item::with(['requisitions' => function($q) {
            $q->orderBy('requested_date')->with('department');
        }])->where('id', $this->selectedItem)
        ->chunk(1000, function($items) use (&$allMovements) {
            foreach($items as $item) {
                $requisitions = $item->requisitions->map(function ($requisition) {
                    return [
                        'date' => $requisition->requested_date,
                        'document_number' => $requisition->requisition_number,
                        'type' => 'out',
                        'in_quantity' => null,
                        'out_quantity' => $requisition->quantity,
                        'description' => $requisition->department->name
                    ];
                });
                $allMovements = $allMovements->concat($requisitions);
            }
        });

        // Process trusts in chunks
        Item::with(['trusts' => function($q) {
            $q->orderBy('requested_date')->with('requester');
        }])->where('id', $this->selectedItem)
        ->chunk(1000, function($items) use (&$allMovements) {
            foreach($items as $item) {
                $trusts = $item->trusts->map(function ($trust) {
                    return [
                        'date' => $trust->requested_date,
                        'document_number' => $trust->requisition_number,
                        'type' => 'out',
                        'in_quantity' => null,
                        'out_quantity' => $trust->quantity,
                        'description' => $trust->requester->name
                    ];
                });
                $allMovements = $allMovements->concat($trusts);
            }
        });

        // Sort and convert to array
        $allMovements = $allMovements
            ->sortBy('date')
            ->values()
            ->toArray();

        // Calculate running balance
        $balance = 0;
        foreach ($allMovements as &$movement) {
            if ($movement['type'] === 'in') {
                $balance += $movement['in_quantity'];
            } else {
                $balance -= $movement['out_quantity'];
            }
            $movement['balance'] = $balance;
        }

        $this->movements = $allMovements;
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $this->totalIn = collect($this->movements)->sum('in_quantity') ?? 0;
        $this->totalOut = collect($this->movements)->sum('out_quantity') ?? 0;
        $this->balance = $this->totalIn - $this->totalOut;
    }

    public function exportToExcel()
    {
        $movements = collect($this->movements)->map(function ($movement) {
            return [
                'Date' => date('d/m/Y', strtotime($movement['date'])),
                'Document No.' => $movement['document_number'],
                'Department' => $movement['description'],
                'In' => $movement['in_quantity'] ?? '-',
                'Out' => $movement['out_quantity'] ?? '-',
                'Balance' => $movement['balance']
            ];
        });

        // Add totals row
        $movements->push([
            'Date' => '',
            'Document No.' => '',
            'Department' => 'Total',
            'In' => $this->totalIn,
            'Out' => $this->totalOut,
            'Balance' => $this->balance
        ]);

        $fileName = 'item_movements_' . $this->selectedItemCode . '_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new class($movements, $this->itemSearch, $this->selectedItemCode) implements
            \Maatwebsite\Excel\Concerns\FromCollection,
            \Maatwebsite\Excel\Concerns\WithHeadings,
            \Maatwebsite\Excel\Concerns\WithStyles,
            \Maatwebsite\Excel\Concerns\WithTitle
        {
            private $movements;
            private $itemName;
            private $itemCode;

            public function __construct($movements, $itemName, $itemCode)
            {
                $this->movements = $movements;
                $this->itemName = $itemName;
                $this->itemCode = $itemCode;
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
                    ['Date', 'Document No.', 'Description', 'In', 'Out', 'Balance'],
                ];
            }

            public function styles($sheet)
            {
                // Merge cells for title and item code
                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');

                // Title styling
                $sheet->getStyle('A1:F2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                        ],
                    ],
                ]);

                // Headers styling (row 3)
                $sheet->getStyle('A3:F3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4B5563'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Data styling
                $lastRow = $this->movements->count() + 3;
                $dataRange = 'A4:F' . $lastRow;
                
                // Base style for all data cells
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Format column D as text
                $sheet->getStyle('B4:F' . $lastRow)->getNumberFormat()
                    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);

                // Zebra striping for data rows
                for ($row = 4; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F3F4F6'],
                            ],
                        ]);
                    }
                }

                // Left align text columns
                $sheet->getStyle('A4:C' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Center align numeric columns
                $sheet->getStyle('D4:F' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // First set all text to black
                $sheet->getStyle($dataRange)->getFont()->getColor()->setARGB('FF000000');

                // Then style only "In" values in red (column D)
                for ($row = 4; $row <= $lastRow; $row++) {
                    $value = $sheet->getCell('D' . $row)->getValue();
                    if ($value !== '-' && is_numeric($value)) {
                        $sheet->getStyle('D' . $row)->getFont()->getColor()->setARGB('FFFF0000');
                    }
                }

                // Style the totals row
                $sheet->getStyle("A{$lastRow}:F{$lastRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4B5563'],
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE,
                        ],
                    ],
                ]);

                // Make "In" total red in the totals row
                $sheet->getStyle("D{$lastRow}")->getFont()->getColor()->setARGB('FFFF0000');

                // Set column widths
                $sheet->getColumnDimension('A')->setWidth(12); // Date
                $sheet->getColumnDimension('B')->setWidth(15); // Document No
                $sheet->getColumnDimension('C')->setWidth(30); // Department
                $sheet->getColumnDimension('D')->setWidth(12); // In
                $sheet->getColumnDimension('E')->setWidth(12); // Out
                $sheet->getColumnDimension('F')->setWidth(12); // Balance

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
        return view('livewire.item-card')->layout('layouts.app');
    }
}
