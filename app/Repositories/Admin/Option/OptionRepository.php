<?php

namespace App\Repositories\Admin\Option;

use App\Models\Admin\Option\Option;
use Illuminate\Validation\ValidationException;

class OptionRepository
{
    /**
     * Get all options with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOptions(array $filters = [])
    {
        $query = Option::query();

        // Apply status filter
        if (isset($filters['is_active']) && !empty($filters['is_active'])) {
            $statusValues = is_array($filters['is_active']) ? $filters['is_active'] : [$filters['is_active']];
            $statusBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $statusValues);
            $query->whereIn('is_active', $statusBooleans);
        }

        // Apply required filter
        if (isset($filters['is_required']) && !empty($filters['is_required'])) {
            $requiredValues = is_array($filters['is_required']) ? $filters['is_required'] : [$filters['is_required']];
            $requiredBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $requiredValues);
            $query->whereIn('is_required', $requiredBooleans);
        }

        // Apply type filter
        if (isset($filters['type']) && !empty($filters['type'])) {
            $types = is_array($filters['type']) ? $filters['type'] : [$filters['type']];
            $query->whereIn('type', $types);
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
     * Get an option by ID.
     *
     * @param int $id
     * @return Option
     */
    public function getOptionById($id)
    {
        return Option::findOrFail($id);
    }

    /**
     * Create a new option.
     *
     * @param array $data
     * @return Option
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $optionData = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'text',
            'is_active' => $data['is_active'] ?? true,
            'is_required' => $data['is_required'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle value field based on type
        if (isset($data['value'])) {
            if (in_array($data['type'], ['select', 'multiselect', 'radio', 'checkbox']) && is_array($data['value'])) {
                $optionData['value'] = json_encode($data['value']);
            } else {
                $optionData['value'] = $data['value'];
            }
        }

        return Option::create($optionData);
    }

    /**
     * Update an option.
     *
     * @param int $id
     * @param array $data
     * @return Option
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $option = Option::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? $option->type,
            'is_active' => $data['is_active'] ?? $option->is_active,
            'is_required' => $data['is_required'] ?? $option->is_required,
            'sort_order' => $data['sort_order'] ?? $option->sort_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $option->slug) {
            $updateData['slug'] = $data['slug'];
        }

        // Handle value field based on type
        if (isset($data['value'])) {
            if (in_array($data['type'] ?? $option->type, ['select', 'multiselect', 'radio', 'checkbox']) && is_array($data['value'])) {
                $updateData['value'] = json_encode($data['value']);
            } else {
                $updateData['value'] = $data['value'];
            }
        }

        $option->update($updateData);
        return $option->fresh();
    }

    /**
     * Delete an option.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $option = Option::findOrFail($id);
        return $option->delete();
    }

    /**
     * Bulk delete options.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $options = Option::whereIn('id', $ids)->get();

        foreach ($options as $option) {
            $option->delete();
        }

        return true;
    }

    /**
     * Toggle option active status.
     *
     * @param int $id
     * @return Option
     */
    public function toggleStatus($id)
    {
        $option = Option::findOrFail($id);
        $option->update(['is_active' => !$option->is_active]);
        return $option;
    }

    /**
     * Toggle option required status.
     *
     * @param int $id
     * @return Option
     */
    public function toggleRequired($id)
    {
        $option = Option::findOrFail($id);
        $option->update(['is_required' => !$option->is_required]);
        return $option;
    }

    /**
     * Update sort order for multiple options.
     *
     * @param array $sortData Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder(array $sortData)
    {
        foreach ($sortData as $id => $sortOrder) {
            Option::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
        return true;
    }

    /**
     * Get options for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getOptionsForDropdown()
    {
        return Option::active()
            ->ordered()
            ->get()
            ->map(function ($option) {
                return [
                    'id' => $option->id,
                    'name' => $option->name,
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
            'total' => Option::count(),
            'active' => Option::active()->count(),
            'required' => Option::required()->count(),
        ];
    }

    /**
     * Validate option data.
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
            'slug' => 'nullable|string|max:255|unique:options,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:text,select,multiselect,radio,checkbox',
            'value' => 'nullable',
            'is_active' => 'boolean',
            'is_required' => 'boolean',
            'sort_order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:options,slug,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}

