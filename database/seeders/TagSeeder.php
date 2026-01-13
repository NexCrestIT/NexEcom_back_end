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
                'name' => 'Long Lasting',
                'slug' => 'long-lasting',
                'description' => 'Fragrances with exceptional longevity',
                'color' => '#FF6B6B',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Date Night',
                'slug' => 'date-night',
                'description' => 'Seductive scents perfect for romantic evenings',
                'color' => '#4ECDC4',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Office Wear',
                'slug' => 'office-wear',
                'description' => 'Professional and subtle fragrances for work',
                'color' => '#95E1D3',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Luxury',
                'slug' => 'luxury',
                'description' => 'Premium haute perfumery',
                'color' => '#F38181',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Bestseller',
                'slug' => 'bestseller',
                'description' => 'Top-selling fragrances',
                'color' => '#AAE3E2',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'New Launch',
                'slug' => 'new-launch',
                'description' => 'Latest perfume releases',
                'color' => '#FFD93D',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Gift Ready',
                'slug' => 'gift-ready',
                'description' => 'Perfect fragrance gifts',
                'color' => '#6BCB77',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Seasonal',
                'slug' => 'seasonal',
                'description' => 'Season-specific scents',
                'color' => '#4D96FF',
                'is_active' => true,
                'sort_order' => 8,
            ],
            [
                'name' => 'Unisex',
                'slug' => 'unisex',
                'description' => 'Gender-neutral fragrances',
                'color' => '#A78BFA',
                'is_active' => true,
                'sort_order' => 9,
            ],
            [
                'name' => 'Fresh',
                'slug' => 'fresh',
                'description' => 'Clean and refreshing scents',
                'color' => '#34D399',
                'is_active' => true,
                'sort_order' => 10,
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
