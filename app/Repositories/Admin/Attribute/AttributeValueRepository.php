<?php

namespace App\Repositories\Admin\Attribute;

use App\Models\Admin\Attribute\AttributeValue;
use Illuminate\Validation\ValidationException;

class AttributeValueRepository
{
    /**
     * Get all values for an attribute.
     *
     * @param int $attributeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getValuesByAttribute($attributeId)
    {
        return AttributeValue::where('attribute_id', $attributeId)
            ->ordered()
            ->get();
    }

    /**
     * Get an attribute value by ID.
     *
     * @param int $id
     * @return AttributeValue
     */
    public function getValueById($id)
    {
        return AttributeValue::findOrFail($id);
    }

    /**
     * Create a new attribute value.
     *
     * @param array $data
     * @return AttributeValue
     */
    public function store($data)
    {
        $this->validateData($data);
        return AttributeValue::create([
            'attribute_id' => $data['attribute_id'],
            'value' => $data['value'],
            'slug' => $data['slug'] ?? null,
            'display_value' => $data['display_value'] ?? null,
            'color_code' => $data['color_code'] ?? null,
            'image' => $data['image'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * Update an attribute value.
     *
     * @param int $id
     * @param array $data
     * @return AttributeValue
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $attributeValue = AttributeValue::findOrFail($id);
        
        $updateData = [
            'value' => $data['value'],
            'display_value' => $data['display_value'] ?? null,
            'color_code' => $data['color_code'] ?? null,
            'image' => $data['image'] ?? null,
            'is_active' => $data['is_active'] ?? $attributeValue->is_active,
            'sort_order' => $data['sort_order'] ?? $attributeValue->sort_order,
        ];

        // Update attribute_id only if provided and different
        if (isset($data['attribute_id']) && $data['attribute_id'] !== $attributeValue->attribute_id) {
            $updateData['attribute_id'] = $data['attribute_id'];
        }

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $attributeValue->slug) {
            $updateData['slug'] = $data['slug'];
        }

        $attributeValue->update($updateData);
        return $attributeValue->fresh();
    }

    /**
     * Delete an attribute value.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $attributeValue = AttributeValue::findOrFail($id);
        return $attributeValue->delete();
    }

    /**
     * Validate attribute value data.
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
            'attribute_id' => 'required|integer|exists:attributes,id',
            'value' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'display_value' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'image' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        if ($isUpdate && $id) {
            // For updates, check unique value per attribute (excluding current record)
            $attributeId = $data['attribute_id'] ?? AttributeValue::find($id)->attribute_id;
            $rules['value'] = 'required|string|max:255|unique:attribute_values,value,' . $id . ',id,attribute_id,' . $attributeId;
        } else {
            // For creates, check unique value per attribute
            $rules['value'] = 'required|string|max:255|unique:attribute_values,value,NULL,id,attribute_id,' . $data['attribute_id'];
        }

        return validator($data, $rules)->validate();
    }
}

