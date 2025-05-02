<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;

class ManagementSuppliers extends Component
{
    public $suppliers;
    public $newSupplier = [
        'id' => null,
        'name' => '',
        'contact_info' => '',
    ];
    public $nameFilter = '';
    public $contactFilter = '';
    protected $listeners = ['deleteSupplier'];

    public function mount()
    {
        $this->refreshSuppliers();
    }

    public function addSupplier()
    {
        Supplier::create($this->newSupplier);
        $this->resetNewSupplier();
        $this->refreshSuppliers();
    }

    public function updateSupplier()
    {
        $supplier = Supplier::find($this->newSupplier['id']);
        if ($supplier) {
            $supplier->update($this->newSupplier);
            $this->resetNewSupplier();
            $this->refreshSuppliers();
        } else {
            session()->flash('error', 'Supplier not found.');
        }
    }

    public function edit($id)
    {
        $supplier = Supplier::find($id);
        $this->newSupplier = [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'contact_info' => $supplier->contact_info,
        ];
    }

    public function deleteSupplier($id)
    {
        Supplier::destroy($id);
        $this->refreshSuppliers();
    }

    public function refreshSuppliers()
    {
        $this->suppliers = Supplier::when($this->nameFilter, function ($query) {
            return $query->where('name', 'like', '%' . $this->nameFilter . '%');
        })->when($this->contactFilter, function ($query) {
            return $query->where('contact_info', 'like', '%' . $this->contactFilter . '%');
        })->get();
    }

    public function resetNewSupplier()
    {
        $this->newSupplier = [
            'name' => '',
            'contact_info' => '',
        ];
    }

    public function updatedNameFilter()
    {
        $this->refreshSuppliers();
    }

    public function updatedContactFilter()
    {
        $this->refreshSuppliers();
    }

    public function render()
    {
        return view('livewire.management-suppliers')->layout('layouts.app');
    }
}