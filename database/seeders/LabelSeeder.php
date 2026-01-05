<?php

namespace Database\Seeders;

use App\Models\Admin\Label\Label;
use Illuminate\Database\Seeder;

class LabelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $labels = [
            'New',
            'Hot',
            'Sale',
            'Featured',
            'Best Seller',
            'Limited Edition',
            'Popular',
            'Trending',
            'Exclusive',
            'Clearance',
        ];

        foreach ($labels as $index => $labelName) {
            Label::updateOrCreate(
                ['name' => $labelName],
                [
                    'name' => $labelName,
                ]
            );
        }
    }
}
