<?php

namespace Database\Seeders;

use App\Models\Admin\Discount\Discount;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DiscountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discounts = [
            [
                'name' => 'Welcome Discount',
                'code' => 'WELCOME10',
                'description' => '10% off on your first order',
                'type' => 'percentage',
                'value' => 10.00,
                'minimum_purchase' => 50.00,
                'maximum_discount' => 100.00,
                'usage_limit_per_user' => 1,
                'total_usage_limit' => 1000,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(365),
                'is_active' => true,
                'is_first_time_only' => true,
                'free_shipping' => false,
                'sort_order' => 1,
                'meta_title' => 'Welcome Discount - 10% Off',
                'meta_description' => 'Get 10% off on your first order with code WELCOME10',
            ],
            [
                'name' => 'Summer Sale',
                'code' => 'SUMMER25',
                'description' => '25% off on summer collection',
                'type' => 'percentage',
                'value' => 25.00,
                'minimum_purchase' => 100.00,
                'maximum_discount' => 500.00,
                'usage_limit_per_user' => 3,
                'total_usage_limit' => 5000,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(60),
                'is_active' => true,
                'is_first_time_only' => false,
                'free_shipping' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Free Shipping',
                'code' => 'FREESHIP',
                'description' => 'Free shipping on orders over $75',
                'type' => 'fixed',
                'value' => 0.00,
                'minimum_purchase' => 75.00,
                'maximum_discount' => null,
                'usage_limit_per_user' => null,
                'total_usage_limit' => null,
                'start_date' => Carbon::now()->subDays(7),
                'end_date' => Carbon::now()->addDays(180),
                'is_active' => true,
                'is_first_time_only' => false,
                'free_shipping' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Flash Sale',
                'code' => 'FLASH50',
                'description' => '50% off on selected items',
                'type' => 'percentage',
                'value' => 50.00,
                'minimum_purchase' => 200.00,
                'maximum_discount' => 1000.00,
                'usage_limit_per_user' => 1,
                'total_usage_limit' => 100,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(3),
                'is_active' => true,
                'is_first_time_only' => false,
                'free_shipping' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Student Discount',
                'code' => 'STUDENT15',
                'description' => '15% off for students',
                'type' => 'percentage',
                'value' => 15.00,
                'minimum_purchase' => 30.00,
                'maximum_discount' => 200.00,
                'usage_limit_per_user' => 5,
                'total_usage_limit' => 2000,
                'start_date' => Carbon::now()->subDays(15),
                'end_date' => Carbon::now()->addDays(365),
                'is_active' => true,
                'is_first_time_only' => false,
                'free_shipping' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'Holiday Special',
                'code' => 'HOLIDAY30',
                'description' => '30% off on holiday collection',
                'type' => 'percentage',
                'value' => 30.00,
                'minimum_purchase' => 150.00,
                'maximum_discount' => 750.00,
                'usage_limit_per_user' => 2,
                'total_usage_limit' => 3000,
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(90),
                'is_active' => false,
                'is_first_time_only' => false,
                'free_shipping' => false,
                'sort_order' => 6,
            ],
            [
                'name' => 'Clearance Sale',
                'code' => 'CLEARANCE',
                'description' => '$20 off on clearance items',
                'type' => 'fixed',
                'value' => 20.00,
                'minimum_purchase' => 100.00,
                'maximum_discount' => null,
                'usage_limit_per_user' => null,
                'total_usage_limit' => null,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addDays(30),
                'is_active' => true,
                'is_first_time_only' => false,
                'free_shipping' => false,
                'sort_order' => 7,
            ],
        ];

        foreach ($discounts as $discountData) {
            Discount::updateOrCreate(
                ['code' => $discountData['code']],
                $discountData
            );
        }
    }
}
