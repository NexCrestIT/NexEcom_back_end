<?php

namespace Database\Seeders;

use App\Models\Admin\Collection\Collection;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $collections = [
            [
                'name' => 'New Fragrances',
                'slug' => 'new-fragrances',
                'description' => 'Discover our latest perfume launches and newest additions',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'New Fragrances - Latest Perfumes',
                'meta_description' => 'Shop the newest perfumes in our collection.',
            ],
            [
                'name' => 'Best Sellers',
                'slug' => 'best-sellers',
                'description' => 'Our most popular and best-selling fragrances',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'meta_title' => 'Best Selling Perfumes',
                'meta_description' => 'Browse our best-selling perfumes loved by customers worldwide.',
            ],
            [
                'name' => 'Signature Collection',
                'slug' => 'signature-collection',
                'description' => 'Iconic signature fragrances for making a statement',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Niche Perfumes',
                'slug' => 'niche-perfumes',
                'description' => 'Exclusive artisanal niche fragrances',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Gift Sets',
                'slug' => 'gift-sets',
                'description' => 'Curated perfume gift sets for every occasion',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Luxury Collection',
                'slug' => 'luxury-collection',
                'description' => 'Premium haute perfumery and luxury fragrances',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Summer Scents',
                'slug' => 'summer-scents',
                'description' => 'Light and refreshing fragrances perfect for summer',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Limited Edition',
                'slug' => 'limited-edition',
                'description' => 'Exclusive limited edition perfumes and rare finds',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($collections as $collectionData) {
            Collection::updateOrCreate(
                ['slug' => $collectionData['slug']],
                $collectionData
            );
        }
    }
}
