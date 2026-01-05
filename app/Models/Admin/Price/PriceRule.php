<?php

namespace App\Models\Admin\Price;

use App\Models\Admin\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'price_list_id',
        'product_id',
        'price',
        'compare_at_price',
        'min_quantity',
        'max_quantity',
        'customer_group',
        'region',
        'currency',
        'is_active',
        'sort_order',
        'valid_from',
        'valid_to',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'min_quantity' => 'integer',
        'max_quantity' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
    ];

    /**
     * Price list relationship.
     */
    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    /**
     * Product relationship.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Price history relationship.
     */
    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Check if price rule is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_to && $now->gt($this->valid_to)) {
            return false;
        }

        return true;
    }

    /**
     * Check if quantity is within range.
     */
    public function isQuantityValid(int $quantity): bool
    {
        if ($quantity < $this->min_quantity) {
            return false;
        }
        if ($this->max_quantity && $quantity > $this->max_quantity) {
            return false;
        }
        return true;
    }

    /**
     * Scope to filter active price rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by customer group.
     */
    public function scopeForCustomerGroup($query, $customerGroup)
    {
        return $query->where(function ($q) use ($customerGroup) {
            $q->whereNull('customer_group')
              ->orWhere('customer_group', $customerGroup);
        });
    }

    /**
     * Scope to filter by region.
     */
    public function scopeForRegion($query, $region)
    {
        return $query->where(function ($q) use ($region) {
            $q->whereNull('region')
              ->orWhere('region', $region);
        });
    }

    /**
     * Scope to filter valid price rules.
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')
                  ->orWhere('valid_to', '>=', $now);
            });
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')
            ->orderBy('min_quantity', 'asc');
    }
}
