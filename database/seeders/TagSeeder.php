<?php

namespace Database\Seeders;

use App\Models\Admin\Tag\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            [
                'name' => 'Summer Collection',
                'slug' => 'summer-collection',
                'description' => 'Products perfect for summer',
                'color' => '#FF6B6B',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Winter Sale',
                'slug' => 'winter-sale',
                'description' => 'Winter season special offers',
                'color' => '#4ECDC4',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Eco Friendly',
                'slug' => 'eco-friendly',
                'description' => 'Environmentally friendly products',
                'color' => '#95E1D3',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'description' => 'Premium quality products',
                'color' => '#F38181',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Budget Friendly',
                'slug' => 'budget-friendly',
                'description' => 'Affordable products for everyone',
                'color' => '#AAE3E2',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'New Arrival',
                'slug' => 'new-arrival',
                'description' => 'Latest products in store',
                'color' => '#FFD93D',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Gift Ideas',
                'slug' => 'gift-ideas',
                'description' => 'Perfect gifts for your loved ones',
                'color' => '#6BCB77',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Tech',
                'slug' => 'tech',
                'description' => 'Technology related products',
                'color' => '#4D96FF',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($tags as $tagData) {
            Tag::updateOrCreate(
                ['slug' => $tagData['slug']],
                $tagData
            );
        }
    }
}
