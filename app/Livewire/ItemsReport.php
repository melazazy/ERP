<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Item;
use App\Exports\ItemsReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ItemsReport extends Component
{
    public $items;

    public function mount()
    {
        $this->items = Item::with([
            'receivings.department',
            'requisitions.department',
            'trusts.department',
        ])
        ->withSum('receivings as total_receivings', 'quantity')
        ->withAvg('receivings as avg_receiving_price', 'unit_price')
        ->withSum('requisitions as total_requisitions', 'quantity')
        ->withSum('trusts as total_trusts', 'quantity')
        ->get();
    }

    public function getPreparedItems()
    {
        return $this->items->map(function($item) {
            $deptCounts = collect();
            foreach ($item->receivings as $rec) {
                if ($rec->department) $deptCounts->push($rec->department->name);
            }
            foreach ($item->requisitions as $req) {
                if ($req->department) $deptCounts->push($req->department->name);
            }
            foreach ($item->trusts as $trust) {
                if ($trust->department) $deptCounts->push($trust->department->name);
            }
            $allDepartments = $deptCounts->unique()->values();
            $mainDepartment = $deptCounts->countBy()->sortDesc()->keys()->first();
            $otherDepartments = $allDepartments->filter(fn($d) => $d !== $mainDepartment)->values();

            $total_receivings = $item->total_receivings ?? 0;
            $avg_receiving_price = $item->avg_receiving_price ?? 0;
            $total_requisitions = $item->total_requisitions ?? 0;
            $total_trusts = $item->total_trusts ?? 0;
            $total_receivings_value = $item->receivings->sum(function($rec) {
                return ($rec->quantity ?? 0) * ($rec->unit_price ?? 0);
            });
            $balance = $total_receivings - ($total_requisitions + $total_trusts);

            return [
                'name' => $item->name,
                'main_department' => $mainDepartment,
                'other_departments' => $otherDepartments->isNotEmpty() ? $otherDepartments->implode(', ') : '-',
                'total_receivings' => $total_receivings,
                'avg_receiving_price' => $avg_receiving_price,
                'total_receivings_value' => $total_receivings_value,
                'total_requisitions' => $total_requisitions,
                'total_trusts' => $total_trusts,
                'balance' => $balance,
            ];
        });
    }

    public function export()
    {
        // Fetch fresh, full dataset for export
        $items = Item::with([
            'receivings.department',
            'requisitions.department',
            'trusts.department',
        ])
        ->withSum('receivings as total_receivings', 'quantity')
        ->withAvg('receivings as avg_receiving_price', 'unit_price')
        ->withSum('requisitions as total_requisitions', 'quantity')
        ->withSum('trusts as total_trusts', 'quantity')
        ->get();

        $prepared = $items->map(function($item) {
            $deptCounts = collect();
            foreach ($item->receivings as $rec) {
                if ($rec->department) $deptCounts->push($rec->department->name);
            }
            foreach ($item->requisitions as $req) {
                if ($req->department) $deptCounts->push($req->department->name);
            }
            foreach ($item->trusts as $trust) {
                if ($trust->department) $deptCounts->push($trust->department->name);
            }
            $allDepartments = $deptCounts->unique()->values();
            $mainDepartment = $deptCounts->countBy()->sortDesc()->keys()->first();
            $otherDepartments = $allDepartments->filter(fn($d) => $d !== $mainDepartment)->values();

            $total_receivings = $item->total_receivings ?? 0;
            $avg_receiving_price = $item->avg_receiving_price ?? 0;
            $total_requisitions = $item->total_requisitions ?? 0;
            $total_trusts = $item->total_trusts ?? 0;
            $total_receivings_value = $item->receivings->sum(function($rec) {
                return ($rec->quantity ?? 0) * ($rec->unit_price ?? 0);
            });
            $balance = $total_receivings - ($total_requisitions + $total_trusts);

            return [
                'name' => $item->name,
                'main_department' => $mainDepartment,
                'other_departments' => $otherDepartments->isNotEmpty() ? $otherDepartments->implode(', ') : '-',
                'total_receivings' => $total_receivings,
                'avg_receiving_price' => $avg_receiving_price,
                'total_receivings_value' => $total_receivings_value,
                'total_requisitions' => $total_requisitions,
                'total_trusts' => $total_trusts,
                'balance' => $balance,
            ];
        })->values()->all();

        return Excel::download(new ItemsReportExport($prepared), 'items_report.xlsx');
    }

    public function render()
    {
        return view('livewire.items-report')->layout('layouts.app');
    }
}