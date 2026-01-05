<?php

namespace App\Models\Admin\Price;

use App\Models\Admin\Product\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = [
        'product_id',
        'price_list_id',
        'price_rule_id',
        'old_price',
        'new_price',
        'compare_at_price',
        'changed_by',
        'reason',
        'notes',
        'changed_at',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'changed_at' => 'datetime',
    ];

    /**
     * Product relationship.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Price list relationship.
     */
    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    /**
     * Price rule relationship.
     */
    public function priceRule()
    {
        return $this->belongsTo(PriceRule::class);
    }

    /**
     * User who made the change.
     */
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * Scope to filter by product.
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to filter by price list.
     */
    public function scopeForPriceList($query, $priceListId)
    {
        return $query->where('price_list_id', $priceListId);
    }

    /**
     * Scope to filter by reason.
     */
    public function scopeByReason($query, $reason)
    {
        return $query->where('reason', $reason);
    }

    /**
     * Scope to filter recent changes.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by change date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('changed_at', 'desc');
    }
}
