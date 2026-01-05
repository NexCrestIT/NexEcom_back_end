<?php

namespace Database\Seeders;

use App\Models\Admin\Brand\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            [
                'name' => 'Apple',
                'slug' => 'apple',
                'description' => 'Innovative technology products',
                'website' => 'https://www.apple.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'Apple Products - Official Store',
                'meta_description' => 'Shop the latest Apple products and accessories.',
            ],
            [
                'name' => 'Samsung',
                'slug' => 'samsung',
                'description' => 'Leading electronics and technology brand',
                'website' => 'https://www.samsung.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Nike',
                'slug' => 'nike',
                'description' => 'Just Do It - Athletic and lifestyle brand',
                'website' => 'https://www.nike.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Adidas',
                'slug' => 'adidas',
                'description' => 'Impossible is Nothing - Sports brand',
                'website' => 'https://www.adidas.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Sony',
                'slug' => 'sony',
                'description' => 'Be Moved - Electronics and entertainment',
                'website' => 'https://www.sony.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'LG',
                'slug' => 'lg',
                'description' => 'Life\'s Good - Electronics and home appliances',
                'website' => 'https://www.lg.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Canon',
                'slug' => 'canon',
                'description' => 'Image Anywhere - Cameras and imaging',
                'website' => 'https://www.canon.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Dell',
                'slug' => 'dell',
                'description' => 'Computers and technology solutions',
                'website' => 'https://www.dell.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
            ],
        ];

        foreach ($brands as $brandData) {
            Brand::updateOrCreate(
                ['slug' => $brandData['slug']],
                $brandData
            );
        }
    }
}
