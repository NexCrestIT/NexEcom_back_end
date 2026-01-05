<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminRoleSeeder::class,
            AdminSeeder::class,
            CategorySeeder::class,
            LabelSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            TagSeeder::class,
            BrandSeeder::class,
            CollectionSeeder::class,
            OptionSeeder::class,
            DiscountSeeder::class,
            FlashSaleSeeder::class,
            InventorySeeder::class,
        ]);
    }
}
