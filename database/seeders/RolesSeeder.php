<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'System Administrator',
                'display_name' => 'مدير النظام',
                'description' => 'صلاحيات كاملة على النظام وإدارة المستخدمين والإعدادات',
                'level' => 10,
                'is_active' => 1,
            ],
            [
                'name' => 'Warehouse Manager',
                'display_name' => 'مدير المخازن',
                'description' => 'إدارة المخازن والأصناف والفئات والموردين',
                'level' => 9,
                'is_active' => 1,
            ],
            [
                'name' => 'Inventory Controller',
                'display_name' => 'مراقب مخازن',
                'description' => 'مراقبة حركة المخزون وإجراء الجرد',
                'level' => 8,
                'is_active' => 1,
            ],
            [
                'name' => 'Accountant',
                'display_name' => 'محاسب',
                'description' => 'إدارة الحسابات والتقارير المالية',
                'level' => 7,
                'is_active' => 1,
            ],
            [
                'name' => 'Department Manager',
                'display_name' => 'مدير قسم',
                'description' => 'إدارة شؤون القسم والموافقة على الطلبات',
                'level' => 6,
                'is_active' => 1,
            ],
            [
                'name' => 'Store Keeper',
                'display_name' => 'موظف مخزن',
                'description' => 'إدارة المخزون وتنفيذ عمليات النقل',
                'level' => 5,
                'is_active' => 1,
            ],
            [
                'name' => 'Receiving Clerk',
                'display_name' => 'موظف استلام',
                'description' => 'تسجيل عمليات الاستلام',
                'level' => 4,
                'is_active' => 1,
            ],
            [
                'name' => 'Requisition Clerk',
                'display_name' => 'موظف طلبات',
                'description' => 'إنشاء ومتابعة الطلبات',
                'level' => 3,
                'is_active' => 1,
            ],
            [
                'name' => 'Trust Clerk',
                'display_name' => 'موظف أمانات',
                'description' => 'إدارة عمليات الأمانات',
                'level' => 2,
                'is_active' => 1,
            ],
            [
                'name' => 'Auditor',
                'display_name' => 'مراجع',
                'description' => 'مراجعة التقارير والعمليات',
                'level' => 1,
                'is_active' => 1,
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'display_name' => $role['display_name'],
                'description' => $role['description'],
                'level' => $role['level'],
                'is_active' => $role['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
} 