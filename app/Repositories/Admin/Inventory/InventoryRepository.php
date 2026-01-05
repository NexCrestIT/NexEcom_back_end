<?php

namespace App\Repositories\Admin\Inventory;

use App\Models\Admin\Inventory\Inventory;
use App\Models\Admin\Inventory\StockMovement;
use App\Models\Admin\Product\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryRepository
{
    /**
     * Get all inventory records with filtering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllInventory(array $filters = [])
    {
        $query = Inventory::with('product');

        // Apply product filter
        if (isset($filters['product_id']) && !empty($filters['product_id'])) {
            $productIds = is_array($filters['product_id']) ? $filters['product_id'] : [$filters['product_id']];
            $query->whereIn('product_id', $productIds);
        }

        // Apply location filter
        if (isset($filters['location']) && !empty($filters['location'])) {
            $locations = is_array($filters['location']) ? $filters['location'] : [$filters['location']];
            $query->whereIn('location', $locations);
        }

        // Apply stock status filter
        if (isset($filters['stock_status']) && !empty($filters['stock_status'])) {
            $statuses = is_array($filters['stock_status']) ? $filters['stock_status'] : [$filters['stock_status']];
            foreach ($statuses as $status) {
                if ($status === 'in_stock') {
                    $query->inStock();
                } elseif ($status === 'out_of_stock') {
                    $query->outOfStock();
                } elseif ($status === 'low_stock') {
                    $query->lowStock();
                }
            }
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get inventory by ID.
     *
     * @param int $id
     * @return Inventory
     */
    public function getInventoryById($id)
    {
        return Inventory::with(['product', 'stockMovements' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(50);
        }])->findOrFail($id);
    }

    /**
     * Get inventory by product ID and location.
     *
     * @param int $productId
     * @param string $location
     * @return Inventory|null
     */
    public function getInventoryByProductAndLocation($productId, $location = 'main')
    {
        return Inventory::where('product_id', $productId)
            ->where('location', $location)
            ->first();
    }

    /**
     * Create or update inventory.
     *
     * @param array $data
     * @return Inventory
     */
    public function store($data)
    {
        $this->validateData($data);

        $inventory = Inventory::updateOrCreate(
            [
                'product_id' => $data['product_id'],
                'location' => $data['location'] ?? 'main',
            ],
            [
                'quantity' => $data['quantity'] ?? 0,
                'reserved_quantity' => $data['reserved_quantity'] ?? 0,
                'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
                'cost_price' => $data['cost_price'] ?? null,
                'batch_number' => $data['batch_number'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        // Create initial stock movement if quantity is set
        if (isset($data['quantity']) && $data['quantity'] > 0) {
            $this->createStockMovement([
                'product_id' => $inventory->product_id,
                'inventory_id' => $inventory->id,
                'type' => 'in',
                'quantity' => $data['quantity'],
                'quantity_before' => 0,
                'quantity_after' => $data['quantity'],
                'location' => $inventory->location,
                'reason' => 'Initial stock',
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
        }

        return $inventory->load('product');
    }

    /**
     * Update inventory.
     *
     * @param int $id
     * @param array $data
     * @return Inventory
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $inventory = Inventory::findOrFail($id);

        $quantityBefore = $inventory->quantity;

        $inventory->update([
            'quantity' => $data['quantity'] ?? $inventory->quantity,
            'reserved_quantity' => $data['reserved_quantity'] ?? $inventory->reserved_quantity,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? $inventory->low_stock_threshold,
            'cost_price' => $data['cost_price'] ?? $inventory->cost_price,
            'batch_number' => $data['batch_number'] ?? $inventory->batch_number,
            'expiry_date' => $data['expiry_date'] ?? $inventory->expiry_date,
            'notes' => $data['notes'] ?? $inventory->notes,
        ]);

        // Create stock movement if quantity changed
        if (isset($data['quantity']) && $data['quantity'] != $quantityBefore) {
            $this->createStockMovement([
                'product_id' => $inventory->product_id,
                'inventory_id' => $inventory->id,
                'type' => 'adjustment',
                'quantity' => abs($data['quantity'] - $quantityBefore),
                'quantity_before' => $quantityBefore,
                'quantity_after' => $data['quantity'],
                'location' => $inventory->location,
                'reason' => $data['reason'] ?? 'Manual adjustment',
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);
        }

        return $inventory->load('product');
    }

    /**
     * Adjust stock quantity.
     *
     * @param int $inventoryId
     * @param int $quantity
     * @param string $type
     * @param array $data
     * @return Inventory
     */
    public function adjustStock($inventoryId, $quantity, $type = 'adjustment', $data = [])
    {
        $inventory = Inventory::findOrFail($inventoryId);
        $quantityBefore = $inventory->quantity;

        if ($type === 'in' || ($type === 'adjustment' && $quantity > 0)) {
            $inventory->add($quantity);
            $quantityAfter = $inventory->quantity;
        } elseif ($type === 'out' || ($type === 'adjustment' && $quantity < 0)) {
            $absQuantity = abs($quantity);
            if (!$inventory->subtract($absQuantity)) {
                throw new \Exception('Insufficient stock available');
            }
            $quantityAfter = $inventory->quantity;
        } else {
            $inventory->quantity = $quantity;
            $inventory->save();
            $quantityAfter = $inventory->quantity;
        }

        // Create stock movement
        $this->createStockMovement([
            'product_id' => $inventory->product_id,
            'inventory_id' => $inventory->id,
            'type' => $type,
            'quantity' => abs($quantity),
            'quantity_before' => $quantityBefore,
            'quantity_after' => $quantityAfter,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'location' => $inventory->location,
            'to_location' => $data['to_location'] ?? null,
            'cost_price' => $data['cost_price'] ?? $inventory->cost_price,
            'reason' => $data['reason'] ?? ucfirst($type),
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
        ]);

        // Update product stock quantity
        $this->syncProductStock($inventory->product_id);

        return $inventory->load('product');
    }

    /**
     * Create stock movement.
     *
     * @param array $data
     * @return StockMovement
     */
    public function createStockMovement($data)
    {
        return StockMovement::create($data);
    }

    /**
     * Get stock movements.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStockMovements(array $filters = [])
    {
        $query = StockMovement::with(['product', 'inventory', 'user']);

        if (isset($filters['product_id']) && !empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['type']) && !empty($filters['type'])) {
            $types = is_array($filters['type']) ? $filters['type'] : [$filters['type']];
            $query->whereIn('type', $types);
        }

        if (isset($filters['location']) && !empty($filters['location'])) {
            $query->where('location', $filters['location']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Sync product stock quantity from inventory.
     *
     * @param int $productId
     * @return void
     */
    public function syncProductStock($productId)
    {
        $product = Product::findOrFail($productId);
        
        if (!$product->track_inventory) {
            return;
        }

        $totalQuantity = Inventory::where('product_id', $productId)
            ->sum('available_quantity');

        $product->stock_quantity = $totalQuantity;
        $product->save();
    }

    /**
     * Get low stock items.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockItems()
    {
        return Inventory::with('product')
            ->lowStock()
            ->orderBy('available_quantity', 'asc')
            ->get();
    }

    /**
     * Get statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_items' => Inventory::count(),
            'in_stock' => Inventory::inStock()->count(),
            'out_of_stock' => Inventory::outOfStock()->count(),
            'low_stock' => Inventory::lowStock()->count(),
            'total_quantity' => Inventory::sum('quantity'),
            'total_reserved' => Inventory::sum('reserved_quantity'),
            'total_available' => Inventory::sum('available_quantity'),
        ];
    }

    /**
     * Validate inventory data.
     *
     * @param array $data
     * @param bool $isUpdate
     * @param int|null $id
     * @return array
     * @throws ValidationException
     */
    public function validateData($data, $isUpdate = false, $id = null)
    {
        $rules = [
            'product_id' => 'required|exists:products,id',
            'location' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:0',
            'reserved_quantity' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'reason' => 'nullable|string|max:255',
        ];

        if ($isUpdate && $id) {
            $rules['product_id'] = 'required|exists:products,id';
        }

        return validator($data, $rules)->validate();
    }
}

