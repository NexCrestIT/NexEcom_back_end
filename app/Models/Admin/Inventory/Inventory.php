<?php

namespace App\Models\Admin\Inventory;

use App\Models\Admin\Product\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $table = 'inventory';

    protected $fillable = [
        'product_id',
        'location',
        'quantity',
        'reserved_quantity',
        'available_quantity',
        'low_stock_threshold',
        'is_low_stock',
        'cost_price',
        'batch_number',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'available_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_low_stock' => 'boolean',
        'cost_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventory) {
            // Calculate available quantity
            $inventory->available_quantity = $inventory->quantity - $inventory->reserved_quantity;
            
            // Check if low stock
            if ($inventory->low_stock_threshold && $inventory->available_quantity <= $inventory->low_stock_threshold) {
                $inventory->is_low_stock = true;
            } else {
                $inventory->is_low_stock = false;
            }
        });
    }

    /**
     * Product relationship.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Stock movements relationship.
     */
    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * Check if inventory is in stock.
     */
    public function isInStock(): bool
    {
        return $this->available_quantity > 0;
    }

    /**
     * Reserve quantity.
     */
    public function reserve(int $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->reserved_quantity += $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Release reserved quantity.
     */
    public function release(int $quantity): bool
    {
        if ($this->reserved_quantity >= $quantity) {
            $this->reserved_quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Add quantity.
     */
    public function add(int $quantity): void
    {
        $this->quantity += $quantity;
        $this->save();
    }

    /**
     * Subtract quantity.
     */
    public function subtract(int $quantity): bool
    {
        if ($this->available_quantity >= $quantity) {
            $this->quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    /**
     * Scope to filter low stock items.
     */
    public function scopeLowStock($query)
    {
        return $query->where('is_low_stock', true);
    }

    /**
     * Scope to filter by location.
     */
    public function scopeLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Scope to filter out of stock items.
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('available_quantity', '<=', 0);
    }

    /**
     * Scope to filter in stock items.
     */
    public function scopeInStock($query)
    {
        return $query->where('available_quantity', '>', 0);
    }
}
