<?php

namespace Database\Seeders;

use App\Models\Admin\FlashSale\FlashSale;
use App\Models\Admin\Product\Product;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FlashSaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $flashSales = [
            [
                'name' => 'Black Friday Flash Sale',
                'slug' => 'black-friday-flash-sale',
                'description' => 'Huge discounts on selected products for Black Friday',
                'start_date' => Carbon::now()->subDays(2),
                'end_date' => Carbon::now()->addDays(1),
                'discount_type' => 'percentage',
                'discount_value' => 50.00,
                'max_products' => 20,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 1,
                'meta_title' => 'Black Friday Flash Sale - 50% Off',
                'meta_description' => 'Get up to 50% off on selected products during our Black Friday flash sale',
            ],
            [
                'name' => 'Weekend Special',
                'slug' => 'weekend-special',
                'description' => 'Special weekend discounts on electronics',
                'start_date' => Carbon::now()->startOfWeek()->addDays(5),
                'end_date' => Carbon::now()->startOfWeek()->addDays(7),
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'max_products' => 15,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'New Year Sale',
                'slug' => 'new-year-sale',
                'description' => 'Start the new year with amazing deals',
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(37),
                'discount_type' => 'fixed',
                'discount_value' => 25.00,
                'max_products' => 25,
                'is_active' => true,
                'is_featured' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Midnight Flash Sale',
                'slug' => 'midnight-flash-sale',
                'description' => 'Limited time midnight deals',
                'start_date' => Carbon::now()->addDays(5)->setTime(0, 0),
                'end_date' => Carbon::now()->addDays(5)->setTime(23, 59),
                'discount_type' => 'percentage',
                'discount_value' => 40.00,
                'max_products' => 10,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Summer Clearance',
                'slug' => 'summer-clearance',
                'description' => 'Clearance sale on summer items',
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->subDays(1),
                'discount_type' => 'percentage',
                'discount_value' => 60.00,
                'max_products' => 30,
                'is_active' => false,
                'is_featured' => false,
                'sort_order' => 5,
            ],
        ];

        foreach ($flashSales as $flashSaleData) {
            $flashSale = FlashSale::updateOrCreate(
                ['slug' => $flashSaleData['slug']],
                $flashSaleData
            );

            // Attach some random products to the flash sale (if products exist)
            $products = Product::inRandomOrder()->limit(rand(3, 8))->get();
            if ($products->count() > 0) {
                $syncData = [];
                foreach ($products as $index => $product) {
                    $syncData[$product->id] = [
                        'discount_type' => rand(0, 1) ? $flashSale->discount_type : null,
                        'discount_value' => rand(0, 1) ? $flashSale->discount_value : null,
                        'sort_order' => $index,
                    ];
                }
                $flashSale->products()->sync($syncData);
            }
        }
    }
}
