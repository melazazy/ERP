<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Role;
use Livewire\WithPagination;

class ManagementRoles extends Component
{
    use WithPagination;

    public $name;
    public $display_name;
    public $description;
    public $level;
    public $is_active = true;
    public $search = '';
    public $editingRoleId = null;
    public $showDeleteModal = false;
    public $roleToDelete = null;

    protected $rules = [
        'name' => 'required|min:3|unique:roles,name',
        'display_name' => 'required|min:3',
        'description' => 'nullable',
        'level' => 'required|integer|min:1',
        'is_active' => 'boolean'
    ];

    public function render()
    {
        $roles = Role::when($this->search, function($query) {
            return $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('display_name', 'like', '%' . $this->search . '%');
        })
        ->orderBy('level', 'desc')
        ->paginate(10);

        return view('livewire.management-roles', [
            'roles' => $roles
        ])->layout('layouts.app');
    }

    public function resetForm()
    {
        $this->reset(['name', 'display_name', 'description', 'level', 'is_active', 'editingRoleId']);
    }

    public function create()
    {
        $this->validate();

        Role::create([
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'level' => $this->level,
            'is_active' => $this->is_active
        ]);

        $this->resetForm();
        session()->flash('message', __('messages.role_created'));
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->editingRoleId = $id;
        $this->name = $role->name;
        $this->display_name = $role->display_name;
        $this->description = $role->description;
        $this->level = $role->level;
        $this->is_active = $role->is_active;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|min:3|unique:roles,name,' . $this->editingRoleId,
            'display_name' => 'required|min:3',
            'description' => 'nullable',
            'level' => 'required|integer|min:1',
            'is_active' => 'boolean'
        ]);

        $role = Role::find($this->editingRoleId);
        $role->update([
            'name' => $this->name,
            'display_name' => $this->display_name,
            'description' => $this->description,
            'level' => $this->level,
            'is_active' => $this->is_active
        ]);

        $this->resetForm();
        session()->flash('message', __('messages.role_updated'));
    }

    public function confirmDelete($id)
    {
        $this->roleToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->roleToDelete) {
            $role = Role::find($this->roleToDelete);
            $role->delete();
            $this->showDeleteModal = false;
            $this->roleToDelete = null;
            session()->flash('message', __('messages.role_deleted'));
        }
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->roleToDelete = null;
    }

    public function toggleStatus($id)
    {
        $role = Role::findOrFail($id);
        $role->update(['is_active' => !$role->is_active]);
        session()->flash('message', __('messages.role_status_updated'));
    }
}
