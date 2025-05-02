<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Department;

class ManagementUsers extends Component
{
    public $users;
    public $newUser = [
        'id' => null,
        'name' => '',
        'email' => '',
        'department_id' => '',
    ];
    public $nameFilter = '';
    public $emailFilter = '';
    public $departments = [];
    protected $listeners = ['deleteUser'];

    public function mount()
    {
        $this->refreshUsers();
        $this->departments = Department::all();
    }

    public function addUser()
    {
        User::create($this->newUser);
        $this->resetNewUser();
        $this->refreshUsers();
    }

    public function updateUser()
    {
        $user = User::find($this->newUser['id']);
        if ($user) {
            $user->update($this->newUser);
            $this->resetNewUser();
            $this->refreshUsers();
        } else {
            session()->flash('error', 'User not found.');
        }
    }

    public function edit($id)
    {
        $user = User::find($id);
        $this->newUser = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'department_id' => $user->department_id,
        ];
    }

    public function deleteUser($id)
    {
        User::destroy($id);
        $this->refreshUsers();
    }

    public function refreshUsers()
    {
        $this->users = User::when($this->nameFilter, function ($query) {
            return $query->where('name', 'like', '%' . $this->nameFilter . '%');
        })->when($this->emailFilter, function ($query) {
            return $query->where('email', 'like', '%' . $this->emailFilter . '%');
        })->get();
    }

    public function resetNewUser()
    {
        $this->newUser = [
            'name' => '',
            'email' => '',
            'department_id' => '',
        ];
    }

    public function updatedNameFilter()
    {
        $this->refreshUsers();
    }

    public function updatedEmailFilter()
    {
        $this->refreshUsers();
    }

    public function render()
    {
        return view('livewire.management-users')->layout('layouts.app');
    }
}