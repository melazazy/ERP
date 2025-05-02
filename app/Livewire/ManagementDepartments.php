<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Department;

class ManagementDepartments extends Component
{
    public $departments;
    public $newDepartment = [
        'id' => null,
        'name' => '',
    ];
    public $nameFilter = '';
    protected $listeners = ['deleteDepartment'];

    public function mount()
    {
        $this->refreshDepartments();
    }

    public function addDepartment()
    {
        Department::create($this->newDepartment);
        $this->resetNewDepartment();
        $this->refreshDepartments();
    }

    public function updateDepartment()
    {
        $department = Department::find($this->newDepartment['id']);
        if ($department) {
            $department->update($this->newDepartment);
            $this->resetNewDepartment();
            $this->refreshDepartments();
        } else {
            session()->flash('error', 'Department not found.');
        }
    }

    public function edit($id)
    {
        $department = Department::find($id);
        $this->newDepartment = [
            'id' => $department->id,
            'name' => $department->name,
        ];
    }

    public function deleteDepartment($id)
    {
        Department::destroy($id);
        $this->refreshDepartments();
    }

    public function refreshDepartments()
    {
        $this->departments = Department::when($this->nameFilter, function ($query) {
            return $query->where('name', 'like', '%' . $this->nameFilter . '%');
        })->get();
    }

    public function resetNewDepartment()
    {
        $this->newDepartment = [
            'name' => '',
        ];
    }

    public function updatedNameFilter()
    {
        $this->refreshDepartments();
    }

    public function render()
    {
        return view('livewire.management-departments')->layout('layouts.app');
    }
}