<?php

namespace App\Traits;

use App\Models\Role;

trait HasRoles
{
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->role->name === $role;
        }
        return $this->role->id === $role->id;
    }

    public function hasAnyRole($roles)
    {
        if (is_string($roles)) {
            return $this->hasRole($roles);
        }
        
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        
        return false;
    }

    public function hasHigherRoleThan($role)
    {
        if (is_string($role)) {
            $role = Role::where('name', $role)->first();
        }
        
        return $this->role->level >= $role->level;
    }

    public function canAccess($permission)
    {
        // Define role-based permissions
        $permissions = [
            'admin' => [
                'manage_users',
                'manage_roles',
                'manage_products',
                'manage_categories',
                'manage_orders',
                'view_reports',
                'manage_settings'
            ],
            'manager' => [
                'manage_products',
                'manage_categories',
                'manage_orders',
                'view_reports'
            ],
            'receiving_clerk' => [
                'manage_products',
                'view_orders'
            ],
            'warehouse_manager' => [
                'manage_products',
                'manage_categories',
                'view_orders',
                'view_reports'
            ],
            'sales_representative' => [
                'view_products',
                'manage_orders'
            ]
        ];

        // Check if user's role has the permission
        return in_array($permission, $permissions[$this->role->name] ?? []);
    }
} 