<?php

namespace App\Helpers;

class RouteHelper
{
    public static function getAvailableRoutes()
    {
        // No need for extensive logging here as the logic will be straightforward.
        $user = auth()->user();

        // If no user or user has no role, return an empty collection.
        if (!$user || !$user->role) {
            return collect([]);
        }

        $userRole = $user->role->name; // Get the user's role name directly, e.g., "System Administrator"

        $allRoutes = collect([
            // Dashboard & Profile - accessible by all authenticated users
            [
                'name' => __('Dashboard'), // Use translation helper for multi-language support
                'route' => 'dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'roles' => ['*'] // Asterisk means all roles
            ],
            [
                'name' => __('Profile'),
                'route' => 'profile',
                'icon' => 'fas fa-user',
                'roles' => ['*']
            ],

            // System Administration Routes
            [
                'name' => __('Role Management'),
                'route' => 'management.roles',
                'icon' => 'fas fa-user-tag',
                'roles' => ['System Administrator']
            ],
            [
                'name' => __('User Management'),
                'route' => 'management.users',
                'icon' => 'fas fa-users',
                'roles' => ['System Administrator']
            ],
            [
                'name' => __('Backup Manager'),
                'route' => 'backup-manager',
                'icon' => 'fas fa-database',
                'roles' => ['System Administrator']
            ],

            // Warehouse Management Routes
            [
                'name' => __('Items Management'),
                'route' => 'management.items',
                'icon' => 'fas fa-boxes',
                'roles' => ['System Administrator', 'Warehouse Manager']
            ],
            [
                'name' => __('Categories'),
                'route' => 'management-category',
                'icon' => 'fas fa-tags',
                'roles' => ['System Administrator', 'Warehouse Manager']
            ],
            [
                'name' => __('Subcategories'),
                'route' => 'management-subcategory',
                'icon' => 'fas fa-tag',
                'roles' => ['System Administrator', 'Warehouse Manager']
            ],
            [
                'name' => __('Departments'),
                'route' => 'management.departments',
                'icon' => 'fas fa-building',
                'roles' => ['System Administrator', 'Warehouse Manager']
            ],
            [
                'name' => __('Suppliers'),
                'route' => 'management.suppliers',
                'icon' => 'fas fa-truck',
                'roles' => ['System Administrator', 'Warehouse Manager']
            ],

            // Receiving Management Routes
            [
                'name' => __('Receiving'),
                'route' => 'receiving',
                'icon' => 'fas fa-receipt',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Receiving Clerk']
            ],
            [
                'name' => __('Receiving Search'),
                'route' => 'receiving-search',
                'icon' => 'fas fa-search',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Receiving Clerk']
            ],

            // Requisition Management Routes
            [
                'name' => __('Requisition'),
                'route' => 'requisition',
                'icon' => 'fas fa-file-invoice',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Requisition Clerk']
            ],
            [
                'name' => __('Requisition Search'),
                'route' => 'requisition-search',
                'icon' => 'fas fa-search',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Requisition Clerk']
            ],
            [
                'name' => __('Transfer'),
                'route' => 'transfer',
                'icon' => 'fas fa-exchange-alt',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Requisition Clerk']
            ],

            // Trust Management Routes
            [
                'name' => __('Trusts'),
                'route' => 'trusts',
                'icon' => 'fas fa-handshake',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Trust Clerk']
            ],
            [
                'name' => __('Trust Search'),
                'route' => 'trust-search',
                'icon' => 'fas fa-search',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Trust Clerk']
            ],

            // Inventory Management Routes
            [
                'name' => __('Item Monitor'),
                'route' => 'item-monitor',
                'icon' => 'fas fa-desktop',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Inventory Controller', 'Store Keeper']
            ],
            [
                'name' => __('Item Report'),
                'route' => 'item-report',
                'icon' => 'fas fa-chart-bar',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Inventory Controller', 'Store Keeper']
            ],

            // Reports and Monitoring Routes
            [
                'name' => __('Inventory Reports'),
                'route' => 'inventory-reports',
                'icon' => 'fas fa-file-alt',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Inventory Controller', 'Accountant', 'Auditor']
            ],
            [
                'name' => __('Department Reports'),
                'route' => 'department-reports',
                'icon' => 'fas fa-building',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Inventory Controller', 'Accountant', 'Auditor']
            ],
            [
                'name' => __('Supplier Reports'),
                'route' => 'supplier-reports',
                'icon' => 'fas fa-truck',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Inventory Controller', 'Accountant', 'Auditor']
            ],
            [
                'name' => __('Export Reports'),
                'route' => 'export-reports',
                'icon' => 'fas fa-file-export',
                'roles' => ['System Administrator', 'Warehouse Manager', 'Inventory Controller', 'Accountant', 'Auditor']
            ],

            // Department Management Routes (System Administrator, Department Manager)
            [
                'name' => __('Department Management'),
                'route' => 'department-management',
                'icon' => 'fas fa-building',
                'roles' => ['System Administrator', 'Department Manager']
            ],

            // Common Routes
            [
                'name' => __('Item Card'),
                'route' => 'item-card',
                'icon' => 'fas fa-id-card',
                'roles' => ['*']
            ],
            [
                'name' => __('Document Search'),
                'route' => 'document-search',
                'icon' => 'fas fa-search',
                'roles' => ['*']
            ],
        ]);

        // Filter the routes based on the user's role.
        return $allRoutes->filter(function ($route) use ($userRole) {
            // A route is available if it's for all roles ('*') or if the user's role is in the route's roles array.
            return in_array('*', $route['roles']) || in_array($userRole, $route['roles']);
        });
    }
}