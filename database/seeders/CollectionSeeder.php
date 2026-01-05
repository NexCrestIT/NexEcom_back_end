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
                'name' => 'New Arrivals',
                'slug' => 'new-arrivals',
                'description' => 'Discover our latest products and newest additions to the store',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'New Arrivals - Latest Products',
                'meta_description' => 'Shop the newest products in our collection.',
            ],
            [
                'name' => 'Best Sellers',
                'slug' => 'best-sellers',
                'description' => 'Our most popular and best-selling products',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'meta_title' => 'Best Sellers - Popular Products',
                'meta_description' => 'Browse our best-selling products loved by customers.',
            ],
            [
                'name' => 'Sale Collection',
                'slug' => 'sale-collection',
                'description' => 'Special offers and discounted products',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Featured Products',
                'slug' => 'featured-products',
                'description' => 'Handpicked featured products',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Gift Collection',
                'slug' => 'gift-collection',
                'description' => 'Perfect gifts for every occasion',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Premium Collection',
                'slug' => 'premium-collection',
                'description' => 'High-end premium products',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Eco Friendly',
                'slug' => 'eco-friendly',
                'description' => 'Environmentally conscious products',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Limited Edition',
                'slug' => 'limited-edition',
                'description' => 'Exclusive limited edition products',
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
