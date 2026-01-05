<?php

namespace Database\Seeders;

use App\Models\Admin\Inventory\Inventory;
use App\Models\Admin\Inventory\StockMovement;
use App\Models\Admin\Product\Product;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::where('track_inventory', true)->limit(20)->get();

        if ($products->isEmpty()) {
            $this->command->warn('No products with inventory tracking found. Please create products first.');
            return;
        }

        foreach ($products as $product) {
            $quantity = rand(10, 500);
            $lowStockThreshold = rand(5, 20);
            $costPrice = $product->cost_price ?? ($product->price * 0.6); // 60% of selling price

            $inventory = Inventory::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'location' => 'main',
                ],
                [
                    'quantity' => $quantity,
                    'reserved_quantity' => rand(0, min(10, $quantity)),
                    'low_stock_threshold' => $lowStockThreshold,
                    'cost_price' => $costPrice,
                    'batch_number' => 'BATCH-' . strtoupper(substr(md5($product->id . time()), 0, 8)),
                    'expiry_date' => rand(0, 1) ? Carbon::now()->addMonths(rand(6, 24)) : null,
                    'notes' => rand(0, 1) ? 'Initial stock entry' : null,
                ]
            );

            // Create initial stock movement
            StockMovement::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'inventory_id' => $inventory->id,
                    'type' => 'in',
                    'quantity' => $quantity,
                    'quantity_before' => 0,
                    'quantity_after' => $quantity,
                    'location' => 'main',
                    'reason' => 'Initial stock',
                    'user_id' => 1,
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                ]
            );

            // Create some random stock movements
            $movementCount = rand(2, 5);
            $currentQuantity = $quantity;

            for ($i = 0; $i < $movementCount; $i++) {
                $movementType = ['in', 'out', 'adjustment'][rand(0, 2)];
                $movementQuantity = rand(1, 50);
                $quantityBefore = $currentQuantity;

                if ($movementType === 'in') {
                    $currentQuantity += $movementQuantity;
                } elseif ($movementType === 'out') {
                    $currentQuantity = max(0, $currentQuantity - $movementQuantity);
                } else {
                    $adjustment = rand(-20, 20);
                    $currentQuantity = max(0, $currentQuantity + $adjustment);
                    $movementQuantity = abs($adjustment);
                }

                StockMovement::create([
                    'product_id' => $product->id,
                    'inventory_id' => $inventory->id,
                    'type' => $movementType,
                    'quantity' => $movementQuantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $currentQuantity,
                    'location' => 'main',
                    'reason' => $this->getRandomReason($movementType),
                    'notes' => rand(0, 1) ? 'Automated stock movement' : null,
                    'user_id' => 1,
                    'created_at' => Carbon::now()->subDays(rand(1, 30))->subHours(rand(1, 23)),
                ]);
            }

            // Update inventory quantity to match movements
            $inventory->quantity = $currentQuantity;
            $inventory->save();
        }

        $this->command->info('Inventory seeded successfully!');
    }

    /**
     * Get random reason for stock movement.
     */
    private function getRandomReason($type): string
    {
        $reasons = [
            'in' => ['Purchase order', 'Return from customer', 'Stock transfer', 'Initial stock'],
            'out' => ['Sale', 'Damage', 'Expired', 'Stock transfer', 'Return to supplier'],
            'adjustment' => ['Stock count correction', 'Manual adjustment', 'System correction', 'Inventory audit'],
        ];

        $typeReasons = $reasons[$type] ?? ['Stock movement'];
        return $typeReasons[array_rand($typeReasons)];
    }
}
