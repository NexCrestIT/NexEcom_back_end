<?php

namespace Database\Seeders;

use App\Models\Admin\Gender\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genders = [
            [
                'name' => 'Male',
                'status' => true,
            ],
            [
                'name' => 'Female',
                'status' => true,
            ],
            [
                'name' => 'Unisex',
                'status' => true,
            ],
        ];

        foreach ($genders as $gender) {
            Gender::create($gender);
        }
    }
}
