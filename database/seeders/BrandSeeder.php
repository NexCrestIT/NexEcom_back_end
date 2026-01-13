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
                'name' => 'Dior',
                'slug' => 'dior',
                'description' => 'French luxury fashion and fragrance house',
                'website' => 'https://www.dior.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'Dior Perfumes - Luxury Fragrances',
                'meta_description' => 'Discover the iconic fragrances from Christian Dior.',
            ],
            [
                'name' => 'Chanel',
                'slug' => 'chanel',
                'description' => 'Timeless elegance in perfumery',
                'website' => 'https://www.chanel.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Tom Ford',
                'slug' => 'tom-ford',
                'description' => 'Modern luxury fragrances with bold sophistication',
                'website' => 'https://www.tomford.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Yves Saint Laurent',
                'slug' => 'yves-saint-laurent',
                'description' => 'Iconic French luxury perfume house',
                'website' => 'https://www.ysl.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Creed',
                'slug' => 'creed',
                'description' => 'Heritage perfume house with artisanal craftsmanship',
                'website' => 'https://www.creedboutique.com',
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Giorgio Armani',
                'slug' => 'giorgio-armani',
                'description' => 'Italian sophistication and elegance in fragrance',
                'website' => 'https://www.giorgioarmani.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Versace',
                'slug' => 'versace',
                'description' => 'Bold and glamorous Italian fragrances',
                'website' => 'https://www.versace.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Gucci',
                'slug' => 'gucci',
                'description' => 'Contemporary luxury Italian perfumes',
                'website' => 'https://www.gucci.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 8,
            ],
            [
                'name' => 'Hermès',
                'slug' => 'hermes',
                'description' => 'Exquisite French fragrances with refined elegance',
                'website' => 'https://www.hermes.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Dolce & Gabbana',
                'slug' => 'dolce-gabbana',
                'description' => 'Passionate Italian perfumes',
                'website' => 'https://www.dolcegabbana.com',
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 10,
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
