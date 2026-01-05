<?php

namespace App\Repositories\Admin\Attribute;

use App\Models\Admin\Attribute\Attribute;
use App\Models\Admin\Attribute\AttributeValue;
use Illuminate\Validation\ValidationException;

class AttributeRepository
{
    /**
     * Get all attributes with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllAttributes(array $filters = [])
    {
        $query = Attribute::query();

        // Apply type filter
        if (isset($filters['type']) && !empty($filters['type'])) {
            $types = is_array($filters['type']) ? $filters['type'] : [$filters['type']];
            $query->whereIn('type', $types);
        }

        // Apply status filter
        if (isset($filters['is_active']) && !empty($filters['is_active'])) {
            $statusValues = is_array($filters['is_active']) ? $filters['is_active'] : [$filters['is_active']];
            $statusBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $statusValues);
            $query->whereIn('is_active', $statusBooleans);
        }

        // Apply filterable filter
        if (isset($filters['is_filterable']) && !empty($filters['is_filterable'])) {
            $filterableValues = is_array($filters['is_filterable']) ? $filters['is_filterable'] : [$filters['is_filterable']];
            $filterableBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $filterableValues);
            $query->whereIn('is_filterable', $filterableBooleans);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->ordered()->get();
    }

    /**
     * Get an attribute by ID.
     *
     * @param int $id
     * @return Attribute
     */
    public function getAttributeById($id)
    {
        return Attribute::with('values')->findOrFail($id);
    }

    /**
     * Create a new attribute.
     *
     * @param array $data
     * @return Attribute
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $attribute = Attribute::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'text',
            'is_required' => $data['is_required'] ?? false,
            'is_filterable' => $data['is_filterable'] ?? true,
            'is_searchable' => $data['is_searchable'] ?? true,
            'is_active' => $data['is_active'] ?? true,
            'default_value' => $data['default_value'] ?? null,
            'validation_rules' => $data['validation_rules'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        // Create attribute values if provided (for select/multiselect types)
        if (isset($data['values']) && is_array($data['values']) && in_array($attribute->type, ['select', 'multiselect'])) {
            $this->syncAttributeValues($attribute->id, $data['values']);
        }

        return $attribute->fresh(['values']);
    }

    /**
     * Update an attribute.
     *
     * @param int $id
     * @param array $data
     * @return Attribute
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $attribute = Attribute::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? $attribute->type,
            'is_required' => $data['is_required'] ?? $attribute->is_required,
            'is_filterable' => $data['is_filterable'] ?? $attribute->is_filterable,
            'is_searchable' => $data['is_searchable'] ?? $attribute->is_searchable,
            'is_active' => $data['is_active'] ?? $attribute->is_active,
            'default_value' => $data['default_value'] ?? null,
            'validation_rules' => $data['validation_rules'] ?? null,
            'sort_order' => $data['sort_order'] ?? $attribute->sort_order,
        ];

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $attribute->slug) {
            $updateData['slug'] = $data['slug'];
        }

        $attribute->update($updateData);

        // Sync attribute values if provided (for select/multiselect types)
        if (isset($data['values']) && is_array($data['values']) && in_array($attribute->type, ['select', 'multiselect'])) {
            $this->syncAttributeValues($attribute->id, $data['values']);
        }

        return $attribute->fresh(['values']);
    }

    /**
     * Delete an attribute.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $attribute = Attribute::findOrFail($id);
        return $attribute->delete();
    }

    /**
     * Bulk delete attributes.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $attributes = Attribute::whereIn('id', $ids)->get();

        foreach ($attributes as $attribute) {
            $attribute->delete();
        }

        return true;
    }

    /**
     * Toggle attribute active status.
     *
     * @param int $id
     * @return Attribute
     */
    public function toggleStatus($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->update(['is_active' => !$attribute->is_active]);
        return $attribute;
    }

    /**
     * Toggle attribute filterable status.
     *
     * @param int $id
     * @return Attribute
     */
    public function toggleFilterable($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->update(['is_filterable' => !$attribute->is_filterable]);
        return $attribute;
    }

    /**
     * Update sort order for multiple attributes.
     *
     * @param array $sortData Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder(array $sortData)
    {
        foreach ($sortData as $id => $sortOrder) {
            Attribute::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
        return true;
    }

    /**
     * Get attributes for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAttributesForDropdown()
    {
        return Attribute::active()
            ->ordered()
            ->get()
            ->map(function ($attribute) {
                return [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                ];
            });
    }

    /**
     * Get statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total' => Attribute::count(),
            'active' => Attribute::active()->count(),
            'filterable' => Attribute::filterable()->count(),
            'by_type' => Attribute::selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }

    /**
     * Validate attribute data.
     *
     * @param array $data
     * @param bool $isUpdate
     * @param int|null $id
     * @return array
     * @throws ValidationException
     */
    public function validateData($data, $isUpdate = false, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:text,number,select,multiselect,boolean,date,textarea',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
            'is_searchable' => 'boolean',
            'is_active' => 'boolean',
            'default_value' => 'nullable|string|max:255',
            'validation_rules' => 'nullable|array',
            'sort_order' => 'integer|min:0',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:attributes,slug,' . $id;
        }

        // If type is select or multiselect, values are required
        if (isset($data['type']) && in_array($data['type'], ['select', 'multiselect'])) {
            if (!isset($data['values']) || empty($data['values'])) {
                throw ValidationException::withMessages([
                    'values' => ['At least one value is required for select/multiselect attributes.']
                ]);
            }
            $rules['values'] = 'required|array|min:1';
            $rules['values.*.value'] = 'required|string|max:255';
            $rules['values.*.display_value'] = 'nullable|string|max:255';
            $rules['values.*.color_code'] = 'nullable|string|max:7';
            $rules['values.*.is_active'] = 'boolean';
            $rules['values.*.sort_order'] = 'integer|min:0';
        }

        return validator($data, $rules)->validate();
    }

    /**
     * Sync attribute values for an attribute.
     *
     * @param int $attributeId
     * @param array $values
     * @return void
     */
    protected function syncAttributeValues($attributeId, array $values)
    {
        $attributeValueRepository = app(\App\Repositories\Admin\Attribute\AttributeValueRepository::class);
        
        // Get existing value IDs
        $existingIds = collect($values)->pluck('id')->filter()->toArray();
        
        // Delete values that are not in the new list
        AttributeValue::where('attribute_id', $attributeId)
            ->whereNotIn('id', $existingIds)
            ->delete();
        
        // Create or update values
        foreach ($values as $valueData) {
            if (isset($valueData['id']) && $valueData['id']) {
                // Update existing
                $attributeValueRepository->update($valueData['id'], array_merge($valueData, ['attribute_id' => $attributeId]));
            } else {
                // Create new
                $attributeValueRepository->store(array_merge($valueData, ['attribute_id' => $attributeId]));
            }
        }
    }
}

