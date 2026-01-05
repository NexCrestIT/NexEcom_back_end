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
        // Root categories
        $electronics = Category::updateOrCreate(
            ['slug' => 'electronics'],
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'Electronics - Shop Latest Gadgets',
                'meta_description' => 'Browse our wide selection of electronic devices and gadgets.',
            ]
        );

        $clothing = Category::updateOrCreate(
            ['slug' => 'clothing'],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel for all',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
                'meta_title' => 'Clothing - Fashion & Apparel',
                'meta_description' => 'Discover the latest fashion trends and clothing.',
            ]
        );

        $homeGarden = Category::updateOrCreate(
            ['slug' => 'home-garden'],
            [
                'name' => 'Home & Garden',
                'description' => 'Everything for your home and garden',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        $sports = Category::updateOrCreate(
            ['slug' => 'sports-outdoors'],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 4,
            ]
        );

        $books = Category::updateOrCreate(
            ['slug' => 'books-media'],
            [
                'name' => 'Books & Media',
                'description' => 'Books, music, and movies',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ]
        );

        // Electronics subcategories
        Category::updateOrCreate(
            ['slug' => 'smartphones'],
            [
                'name' => 'Smartphones',
                'description' => 'Latest smartphones and accessories',
                'parent_id' => $electronics->id,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'laptops'],
            [
                'name' => 'Laptops',
                'description' => 'Laptops and notebooks',
                'parent_id' => $electronics->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ]
        );

        $tablets = Category::updateOrCreate(
            ['slug' => 'tablets'],
            [
                'name' => 'Tablets',
                'description' => 'Tablets and e-readers',
                'parent_id' => $electronics->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'headphones'],
            [
                'name' => 'Headphones',
                'description' => 'Headphones and earbuds',
                'parent_id' => $electronics->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ]
        );

        // Clothing subcategories
        Category::updateOrCreate(
            ['slug' => 'mens-clothing'],
            [
                'name' => "Men's Clothing",
                'description' => "Men's fashion and apparel",
                'parent_id' => $clothing->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'womens-clothing'],
            [
                'name' => "Women's Clothing",
                'description' => "Women's fashion and apparel",
                'parent_id' => $clothing->id,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'kids-clothing'],
            [
                'name' => "Kids' Clothing",
                'description' => "Children's fashion and apparel",
                'parent_id' => $clothing->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        // Home & Garden subcategories
        Category::updateOrCreate(
            ['slug' => 'furniture'],
            [
                'name' => 'Furniture',
                'description' => 'Home furniture',
                'parent_id' => $homeGarden->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'kitchen'],
            [
                'name' => 'Kitchen',
                'description' => 'Kitchen appliances and utensils',
                'parent_id' => $homeGarden->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'garden-tools'],
            [
                'name' => 'Garden Tools',
                'description' => 'Tools for your garden',
                'parent_id' => $homeGarden->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ]
        );

        // Third level - Tablets subcategories
        Category::updateOrCreate(
            ['slug' => 'ipad'],
            [
                'name' => 'iPad',
                'description' => 'Apple iPad tablets',
                'parent_id' => $tablets->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 1,
            ]
        );

        Category::updateOrCreate(
            ['slug' => 'android-tablets'],
            [
                'name' => 'Android Tablets',
                'description' => 'Android-based tablets',
                'parent_id' => $tablets->id,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ]
        );
    }
}

