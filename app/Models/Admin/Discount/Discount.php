<?php

namespace App\Models\Admin\Discount;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Discount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_purchase',
        'maximum_discount',
        'usage_limit_per_user',
        'total_usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
        'is_first_time_only',
        'free_shipping',
        'applicable_categories',
        'applicable_products',
        'excluded_categories',
        'excluded_products',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_purchase' => 'decimal:2',
        'maximum_discount' => 'decimal:2',
        'usage_limit_per_user' => 'integer',
        'total_usage_limit' => 'integer',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'is_first_time_only' => 'boolean',
        'free_shipping' => 'boolean',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
        'excluded_categories' => 'array',
        'excluded_products' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($discount) {
            if (empty($discount->code)) {
                $discount->code = static::generateUniqueCode();
            } else {
                $discount->code = strtoupper($discount->code);
            }
        });

        static::updating(function ($discount) {
            if ($discount->isDirty('code')) {
                $discount->code = strtoupper($discount->code);
            }
        });
    }

    /**
     * Generate a unique discount code.
     */
    public static function generateUniqueCode(): string
    {
        $code = strtoupper(Str::random(8));
        
        while (static::withTrashed()->where('code', $code)->exists()) {
            $code = strtoupper(Str::random(8));
        }

        return $code;
    }

    /**
     * Check if discount is currently valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        if ($this->total_usage_limit && $this->used_count >= $this->total_usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for a given price.
     */
    public function calculateDiscount($price): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->minimum_purchase && $price < $this->minimum_purchase) {
            return 0;
        }

        $discount = 0;
        if ($this->type === 'percentage') {
            $discount = ($price * $this->value) / 100;
        } else {
            $discount = $this->value;
        }

        if ($this->maximum_discount && $discount > $this->maximum_discount) {
            $discount = $this->maximum_discount;
        }

        return min($discount, $price);
    }

    /**
     * Scope to filter active discounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter currently valid discounts.
     */
    public function scopeValid($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where(function($q) {
                $q->whereNull('total_usage_limit')
                  ->orWhereColumn('used_count', '<', 'total_usage_limit');
            });
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to order by sort order and name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}

