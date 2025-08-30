<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            ['name' => 'Piece'],
            ['name' => 'Meter'],
            ['name' => 'Square Meter'],
            ['name' => 'Cubic Meter'],
            ['name' => 'Kilogram'],
            ['name' => 'Ton'],
            ['name' => 'Liter'],
            ['name' => 'bag'],
            ['name' => 'بوكس'],
        ];

        foreach ($units as $unit) {
            DB::table('units')->insert([
                'name' => $unit['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
} 