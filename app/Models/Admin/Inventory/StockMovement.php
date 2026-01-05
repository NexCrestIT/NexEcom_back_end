<?php

namespace App\Models\Admin\Inventory;

use App\Models\Admin\Product\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'product_id',
        'inventory_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'location',
        'to_location',
        'cost_price',
        'reason',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
        'cost_price' => 'decimal:2',
    ];

    /**
     * Product relationship.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Inventory relationship.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * User relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reference model (polymorphic).
     */
    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by location.
     */
    public function scopeLocation($query, $location)
    {
        return $query->where('location', $location);
    }

    /**
     * Scope to filter by product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter recent movements.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'in' => 'Stock In',
            'out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            'transfer' => 'Transfer',
            'return' => 'Return',
            'damage' => 'Damage',
            'expired' => 'Expired',
            default => ucfirst($this->type),
        };
    }
}
