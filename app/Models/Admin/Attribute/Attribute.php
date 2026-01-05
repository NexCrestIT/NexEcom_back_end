<?php

namespace App\Models\Admin\Attribute;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'is_required',
        'is_filterable',
        'is_searchable',
        'is_active',
        'default_value',
        'validation_rules',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'is_searchable' => 'boolean',
        'is_active' => 'boolean',
        'validation_rules' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Boot method for the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attribute) {
            if (empty($attribute->slug)) {
                $attribute->slug = static::generateUniqueSlug($attribute->name);
            }
        });

        static::updating(function ($attribute) {
            if ($attribute->isDirty('name') && !$attribute->isDirty('slug')) {
                $attribute->slug = static::generateUniqueSlug($attribute->name, $attribute->id);
            }
        });
    }

    /**
     * Generate a unique slug for the attribute.
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
     * Scope to filter active attributes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter filterable attributes.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope to filter searchable attributes.
     */
    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
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

    /**
     * Get the attribute values for this attribute.
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->ordered();
    }

    /**
     * Get active attribute values for this attribute.
     */
    public function activeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->where('is_active', true)->ordered();
    }
}
