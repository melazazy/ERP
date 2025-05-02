<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Department;

trait UserDepartmentSearchTrait
{
    protected function searchUsers(string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return [];
        }

        $query = User::query();
        $searchTerms = array_filter(array_map('trim', explode(' ', $searchTerm)));

        foreach ($searchTerms as $term) {
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', '%' . $term . '%')
                   ->orWhere('email', 'like', '%' . $term . '%');
            });
        }

        return $query->select(['id', 'name', 'email'])
                    ->limit(25)
                    ->get()
                    ->toArray();
    }

    protected function searchDepartments(string $searchTerm): array
    {
        if (empty($searchTerm)) {
            return [];
        }

        $query = Department::query();
        $searchTerms = array_filter(array_map('trim', explode(' ', $searchTerm)));

        foreach ($searchTerms as $term) {
            $query->where('name', 'like', '%' . $term . '%');
        }

        return $query->select(['id', 'name'])
                    ->limit(25)
                    ->get()
                    ->toArray();
    }

    public function updatedRequestedBySearch($value)
    {
        $this->users = $this->searchUsers($value);
    }

    public function selectUser($userId)
    {
        $user = User::find($userId);
        if ($user) {
            $this->newTrust['requested_by'] = $userId;
            $this->requestedBySearch = $user->name;
            $this->users = [];
        }
    }

    public function updatedDepartmentSearch($value)
    {
        $this->departments = $this->searchDepartments($value);
    }

    public function selectDepartment($departmentId)
    {
        $department = Department::find($departmentId);
        if ($department) {
            $this->newTrust['department_id'] = $departmentId;
            $this->departmentSearch = $department->name;
            $this->departments = [];
        }
    }
}