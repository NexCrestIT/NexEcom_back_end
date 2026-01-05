<?php

namespace App\Models\Admin\FlashSale;

use App\Models\Admin\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FlashSale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'start_date',
        'end_date',
        'banner_image',
        'discount_type',
        'discount_value',
        'max_products',
        'is_active',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'discount_value' => 'decimal:2',
        'max_products' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['banner_image_url', 'status', 'is_ongoing', 'is_upcoming', 'is_expired'];

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($flashSale) {
            if (empty($flashSale->slug)) {
                $flashSale->slug = static::generateSlug($flashSale->name);
            }
        });

        static::updating(function ($flashSale) {
            if ($flashSale->isDirty('name') && empty($flashSale->slug)) {
                $flashSale->slug = static::generateSlug($flashSale->name);
            }
        });
    }

    /**
     * Generate a unique slug from name.
     */
    public static function generateSlug($name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get banner image URL accessor.
     */
    public function getBannerImageUrlAttribute(): ?string
    {
        if (!$this->banner_image) {
            return null;
        }

        if (filter_var($this->banner_image, FILTER_VALIDATE_URL)) {
            return $this->banner_image;
        }

        return asset('storage/' . $this->banner_image);
    }

    /**
     * Get status attribute (ongoing, upcoming, expired).
     */
    public function getStatusAttribute(): string
    {
        $now = now();
        
        if ($now->lt($this->start_date)) {
            return 'upcoming';
        } elseif ($now->gt($this->end_date)) {
            return 'expired';
        } else {
            return 'ongoing';
        }
    }

    /**
     * Check if flash sale is currently ongoing.
     */
    public function getIsOngoingAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();
        return $now->gte($this->start_date) && $now->lte($this->end_date);
    }

    /**
     * Check if flash sale is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return now()->lt($this->start_date);
    }

    /**
     * Check if flash sale is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return now()->gt($this->end_date);
    }

    /**
     * Check if flash sale is valid (active and within date range).
     */
    public function isValid(): bool
    {
        return $this->is_active && $this->is_ongoing;
    }

    /**
     * Calculate discount for a product.
     */
    public function calculateDiscount($productPrice, $productId = null): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        // Check if product has specific discount in pivot
        if ($productId) {
            $product = $this->products()->where('products.id', $productId)->first();
            if ($product && $product->pivot->discount_type) {
                $discountType = $product->pivot->discount_type;
                $discountValue = $product->pivot->discount_value;
            } else {
                $discountType = $this->discount_type;
                $discountValue = $this->discount_value;
            }
        } else {
            $discountType = $this->discount_type;
            $discountValue = $this->discount_value;
        }

        if (!$discountValue) {
            return 0;
        }

        if ($discountType === 'percentage') {
            return ($productPrice * $discountValue) / 100;
        } else {
            return min($discountValue, $productPrice);
        }
    }

    /**
     * Products relationship (many-to-many).
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'flash_sale_product')
            ->withPivot('discount_type', 'discount_value', 'sort_order')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Scope to filter active flash sales.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter featured flash sales.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to filter ongoing flash sales.
     */
    public function scopeOngoing($query)
    {
        $now = now();
        return $query->where('is_active', true)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope to filter upcoming flash sales.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope to filter expired flash sales.
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Scope to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }
}
