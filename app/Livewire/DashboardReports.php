<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardReports extends Component
{
    public function render()
    {
        $user = Auth::user();
        
        $role = $user->role ? $user->role->name : 'user';

        $reports = $this->getReportsForRole($role);

        return view('livewire.dashboard-reports', [
            'reports' => $reports,
            'user' => $user,
            'role' => $role
        ]);
    }

    private function getReportsForRole($role)
    {
        switch($role) {
            case 'super_admin':
                return [
                    [
                        'title' => 'Total Users',
                        'value' => User::count(),
                        'icon' => 'users',
                        'color' => 'text-indigo-600'
                    ],
                    [
                        'title' => 'Active Requisitions',
                        'value' => 25,
                        'icon' => 'clipboard-list',
                        'color' => 'text-yellow-600'
                    ],
                    [
                        'title' => 'Total Inventory',
                        'value' => 100,
                        'icon' => 'box',
                        'color' => 'text-blue-600'
                    ],
                    [
                        'title' => 'Recent Activity',
                        'value' => 50,
                        'icon' => 'activity',
                        'color' => 'text-purple-600'
                    ]
                ];
    
            case 'inventory_manager':
                return [
                    [
                        'title' => 'Pending Receiving',
                        'value' => 15,
                        'icon' => 'package',
                        'color' => 'text-yellow-600'
                    ],
                    [
                        'title' => 'Total Items',
                        'value' => 50,
                        'icon' => 'box',
                        'color' => 'text-blue-600'
                    ],
                    [
                        'title' => 'Low Stock Items',
                        'value' => 5,
                        'icon' => 'alert-circle',
                        'color' => 'text-red-600'
                    ],
                    [
                        'title' => 'Recent Activity',
                        'value' => 25,
                        'icon' => 'activity',
                        'color' => 'text-purple-600'
                    ]
                ];
    
            case 'requisition_officer':
                return [
                    [
                        'title' => 'My Requisitions',
                        'value' => 10,
                        'icon' => 'clipboard-list',
                        'color' => 'text-yellow-600'
                    ],
                    [
                        'title' => 'Pending Approvals',
                        'value' => 5,
                        'icon' => 'user-plus',
                        'color' => 'text-yellow-600'
                    ],
                    [
                        'title' => 'Available Items',
                        'value' => 30,
                        'icon' => 'box',
                        'color' => 'text-blue-600'
                    ],
                    [
                        'title' => 'Recent Activity',
                        'value' => 15,
                        'icon' => 'activity',
                        'color' => 'text-purple-600'
                    ]
                ];
    
            case 'viewer':
                return [
                    [
                        'title' => 'Total Items',
                        'value' => 50,
                        'icon' => 'box',
                        'color' => 'text-blue-600'
                    ],
                    [
                        'title' => 'Active Requisitions',
                        'value' => 25,
                        'icon' => 'clipboard-list',
                        'color' => 'text-yellow-600'
                    ],
                    [
                        'title' => 'Low Stock Items',
                        'value' => 5,
                        'icon' => 'alert-circle',
                        'color' => 'text-red-600'
                    ],
                    [
                        'title' => 'Recent Activity',
                        'value' => 15,
                        'icon' => 'activity',
                        'color' => 'text-purple-600'
                    ]
                ];
    
            default:
                return [];
        }
    }
}