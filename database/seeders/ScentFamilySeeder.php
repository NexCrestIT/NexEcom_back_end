<?php

namespace Database\Seeders;

use App\Models\Admin\ScentFamily\ScentFamily;
use Illuminate\Database\Seeder;

class ScentFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $scentFamilies = [
            ['name' => 'Floral', 'status' => true],
            ['name' => 'Oriental', 'status' => true],
            ['name' => 'Fresh', 'status' => true],
            ['name' => 'Woody', 'status' => true],
            ['name' => 'Citrus', 'status' => true],
        ];

        foreach ($scentFamilies as $scent) {
            ScentFamily::create($scent);
        }
    }
}
