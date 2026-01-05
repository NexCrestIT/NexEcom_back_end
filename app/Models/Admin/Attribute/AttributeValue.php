<?php

namespace App\Models\Admin\Attribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class AttributeValue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
        'display_value',
        'color_code',
        'image',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attribute_id' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attributeValue) {
            if (empty($attributeValue->slug)) {
                $attributeValue->slug = static::generateUniqueSlug($attributeValue->value, $attributeValue->attribute_id);
            }
        });

        static::updating(function ($attributeValue) {
            if ($attributeValue->isDirty('value') && !$attributeValue->isDirty('slug')) {
                $attributeValue->slug = static::generateUniqueSlug($attributeValue->value, $attributeValue->attribute_id, $attributeValue->id);
            }
        });
    }

    /**
     * Generate a unique slug for the attribute value.
     */
    public static function generateUniqueSlug(string $value, int $attributeId, ?int $excludeId = null): string
    {
        $slug = Str::slug($value);
        $originalSlug = $slug;
        $counter = 1;

        $query = static::withTrashed()->where('attribute_id', $attributeId)->where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $query = static::withTrashed()->where('attribute_id', $attributeId)->where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            $counter++;
        }

        return $slug;
    }

    /**
     * Get the attribute that owns this value.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /**
     * Scope to filter active values.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort order and value.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('value');
    }

    /**
     * Scope to filter by attribute.
     */
    public function scopeForAttribute($query, int $attributeId)
    {
        return $query->where('attribute_id', $attributeId);
    }
}
