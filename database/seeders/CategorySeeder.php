<?php

namespace Database\Seeders;

use App\Models\Admin\Category\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Root categories - Perfume Types
        $eauDeParfum = Category::updateOrCreate(
            ['slug' => 'eau-de-parfum'],
            [
                'name' => 'Eau de Parfum',
                'description' => 'Long-lasting fragrances with 15-20% concentration',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'Eau de Parfum - Luxury Fragrances',
                'meta_description' => 'Discover our collection of premium Eau de Parfum with long-lasting scents.',
            ]
        );

        $eauDeToilette = Category::updateOrCreate(
            ['slug' => 'eau-de-toilette'],
            [
                'name' => 'Eau de Toilette',
                'description' => 'Light and refreshing fragrances with 5-15% concentration',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'meta_title' => 'Eau de Toilette - Fresh Fragrances',
                'meta_description' => 'Browse our selection of Eau de Toilette for everyday wear.',
            ]
        );

        $eauDeCologne = Category::updateOrCreate(
            ['slug' => 'eau-de-cologne'],
            [
                'name' => 'Eau de Cologne',
                'description' => 'Light and citrusy fragrances with 2-5% concentration',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        $parfumExtrait = Category::updateOrCreate(
            ['slug' => 'parfum-extrait'],
            [
                'name' => 'Parfum Extrait',
                'description' => 'Highly concentrated luxury fragrances with 20-40% concentration',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 4,
            ]
        );

        $bodyMist = Category::updateOrCreate(
            ['slug' => 'body-mist'],
            [
                'name' => 'Body Mist',
                'description' => 'Light body sprays and refreshing mists',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ]
        );

        // Eau de Parfum subcategories
        Category::updateOrCreate(
            ['slug' => 'edp-floral'],
            [
                'name' => 'Floral',
                'description' => 'Floral Eau de Parfum fragrances',
                'parent_id' => $eauDeParfum->id,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'edp-oriental'],
            [
                'name' => 'Oriental',
                'description' => 'Exotic Oriental Eau de Parfum',
                'parent_id' => $eauDeParfum->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ]
        );

        $edpWoody = Category::updateOrCreate(
            ['slug' => 'edp-woody'],
            [
                'name' => 'Woody',
                'description' => 'Warm woody Eau de Parfum scents',
                'parent_id' => $eauDeParfum->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'edp-fresh'],
            [
                'name' => 'Fresh',
                'description' => 'Clean and fresh Eau de Parfum',
                'parent_id' => $eauDeParfum->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ]
        );

        // Eau de Toilette subcategories
        Category::updateOrCreate(
            ['slug' => 'edt-citrus'],
            [
                'name' => 'Citrus',
                'description' => 'Citrus-based Eau de Toilette',
                'parent_id' => $eauDeToilette->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'edt-aquatic'],
            [
                'name' => 'Aquatic',
                'description' => 'Fresh aquatic Eau de Toilette',
                'parent_id' => $eauDeToilette->id,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'edt-aromatic'],
            [
                'name' => 'Aromatic',
                'description' => 'Aromatic herbal Eau de Toilette',
                'parent_id' => $eauDeToilette->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        // Parfum Extrait subcategories
        Category::updateOrCreate(
            ['slug' => 'extrait-luxury'],
            [
                'name' => 'Luxury Collection',
                'description' => 'Ultra-premium luxury extraits',
                'parent_id' => $parfumExtrait->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'extrait-niche'],
            [
                'name' => 'Niche Fragrances',
                'description' => 'Exclusive niche perfume extraits',
                'parent_id' => $parfumExtrait->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'extrait-vintage'],
            [
                'name' => 'Vintage Collection',
                'description' => 'Classic vintage perfume extraits',
                'parent_id' => $parfumExtrait->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        // Third level - Woody subcategories
        Category::updateOrCreate(
            ['slug' => 'woody-sandalwood'],
            [
                'name' => 'Sandalwood',
                'description' => 'Sandalwood-based fragrances',
                'parent_id' => $edpWoody->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'woody-cedarwood'],
            [
                'name' => 'Cedarwood',
                'description' => 'Cedarwood-based fragrances',
                'parent_id' => $edpWoody->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ]
        );
    }
}

