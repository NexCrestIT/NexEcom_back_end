<?php

namespace App\Models\Admin\Price;

use App\Models\Admin\Product\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PriceList extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_active',
        'is_default',
        'priority',
        'valid_from',
        'valid_to',
        'applicable_categories',
        'applicable_products',
        'applicable_customer_groups',
        'currency',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'priority' => 'integer',
        'valid_from' => 'datetime',
        'valid_to' => 'datetime',
        'applicable_categories' => 'array',
        'applicable_products' => 'array',
        'applicable_customer_groups' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($priceList) {
            if (empty($priceList->slug)) {
                $priceList->slug = static::generateUniqueSlug($priceList->name);
            }
        });

        static::updating(function ($priceList) {
            if ($priceList->isDirty('name') && empty($priceList->slug)) {
                $priceList->slug = static::generateUniqueSlug($priceList->name, $priceList->id);
            }
        });

        // Ensure only one default price list exists
        static::saving(function ($priceList) {
            if ($priceList->is_default) {
                static::where('id', '!=', $priceList->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Generate a unique slug.
     */
    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::withTrashed()->where('slug', $slug)->when($excludeId, function ($query) use ($excludeId) {
            $query->where('id', '!=', $excludeId);
        })->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Price rules relationship.
     */
    public function priceRules()
    {
        return $this->hasMany(PriceRule::class);
    }

    /**
     * Active price rules relationship.
     */
    public function activePriceRules()
    {
        return $this->hasMany(PriceRule::class)->where('is_active', true);
    }

    /**
     * Price history relationship.
     */
    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Check if price list is currently valid.
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
     * Scope to filter active price lists.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter default price lists.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter valid price lists.
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
     * Scope to order by priority.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('name', 'asc');
    }
}
