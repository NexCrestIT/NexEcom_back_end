<?php

namespace App\Models\Admin\Product;

use App\Models\Admin\Attribute\Attribute;
use App\Models\Admin\Attribute\AttributeValue;
use App\Models\Admin\Brand\Brand;
use App\Models\Admin\Category\Category;
use App\Models\Admin\Collection\Collection;
use App\Models\Admin\Discount\Discount;
use App\Models\Admin\Gender\Gender;
use App\Models\Admin\Label\Label;
use App\Models\Admin\ScentFamily\ScentFamily;
use App\Models\Admin\Tag\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'specifications',
        'price',
        'compare_at_price',
        'cost_price',
        'stock_quantity',
        'track_inventory',
        'low_stock_threshold',
        'allow_backorder',
        'main_image',
        'gallery_images',
        'category_id',
        'brand_id',
        'collection_id',
        'gender_id',
        'scent_family_id',
        'is_active',
        'is_featured',
        'is_new',
        'is_bestseller',
        'is_digital',
        'is_virtual',
        'weight',
        'weight_unit',
        'length',
        'width',
        'height',
        'dimension_unit',
        'taxable',
        'tax_rate',
        'requires_shipping',
        'shipping_weight',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
        'view_count',
        'sold_count',
        'rating',
        'rating_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_inventory' => 'boolean',
        'low_stock_threshold' => 'integer',
        'allow_backorder' => 'boolean',
        'gallery_images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_digital' => 'boolean',
        'is_virtual' => 'boolean',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'taxable' => 'boolean',
        'tax_rate' => 'decimal:2',
        'requires_shipping' => 'boolean',
        'shipping_weight' => 'decimal:2',
        'sort_order' => 'integer',
        'view_count' => 'integer',
        'sold_count' => 'integer',
        'rating' => 'decimal:2',
        'rating_count' => 'integer',
    ];

    protected $appends = ['main_image_url', 'gallery_images_urls', 'is_in_stock', 'is_low_stock', 'discount_percentage'];

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = static::generateUniqueSku();
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id);
            }
        });
    }

    /**
     * Generate a unique slug for the product.
     */
    public static function generateUniqueSlug(string $name, ?int $excludeId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::withTrashed()->where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::withTrashed()->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }

        return $slug;
    }

    /**
     * Generate a unique SKU.
     */
    public static function generateUniqueSku(): string
    {
        $sku = 'PRD-' . strtoupper(Str::random(8));
        
        while (static::withTrashed()->where('sku', $sku)->exists()) {
            $sku = 'PRD-' . strtoupper(Str::random(8));
        }

        return $sku;
    }

    /**
     * Get the main image URL.
     */
    public function getMainImageUrlAttribute()
    {
        if (!$this->main_image) {
            return null;
        }

        if (filter_var($this->main_image, FILTER_VALIDATE_URL)) {
            return $this->main_image;
        }

        return asset('storage/' . $this->main_image);
    }

    /**
     * Get gallery images URLs.
     */
    public function getGalleryImagesUrlsAttribute()
    {
        if (!$this->gallery_images || !is_array($this->gallery_images)) {
            return [];
        }

        return array_map(function ($image) {
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }
            return asset('storage/' . $image);
        }, $this->gallery_images);
    }

    /**
     * Check if product is in stock.
     */
    public function getIsInStockAttribute()
    {
        if (!$this->track_inventory) {
            return true;
        }

        if ($this->allow_backorder) {
            return true;
        }

        return $this->stock_quantity > 0;
    }

    /**
     * Check if product is low stock.
     */
    public function getIsLowStockAttribute()
    {
        if (!$this->track_inventory || !$this->low_stock_threshold) {
            return false;
        }

        return $this->stock_quantity <= $this->low_stock_threshold && $this->stock_quantity > 0;
    }

    /**
     * Calculate discount percentage.
     */
    public function getDiscountPercentageAttribute()
    {
        if (!$this->compare_at_price || $this->compare_at_price <= $this->price) {
            return 0;
        }

        return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100, 2);
    }

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function scentFamily()
    {
        return $this->belongsTo(ScentFamily::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'product_label');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'product_attribute')
            ->withPivot('attribute_value_id', 'value', 'sort_order')
            ->withTimestamps();
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute', 'product_id', 'attribute_value_id')
            ->withPivot('attribute_id', 'value', 'sort_order')
            ->withTimestamps();
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'product_discount');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeBestseller($query)
    {
        return $query->where('is_bestseller', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function($q) {
            $q->where('track_inventory', false)
              ->orWhere('allow_backorder', true)
              ->orWhere('stock_quantity', '>', 0);
        });
    }

    public function scopeLowStock($query)
    {
        return $query->where('track_inventory', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('stock_quantity', '>', 0);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('brand_id', $brandId);
    }

    public function scopeByCollection($query, $collectionId)
    {
        return $query->where('collection_id', $collectionId);
    }

    /**
     * Get the cart items for this product.
     */
    public function cartItems()
    {
        return $this->hasMany(\App\Models\Cart::class);
    }


}

