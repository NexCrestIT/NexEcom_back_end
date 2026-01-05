<?php

namespace App\Repositories\Admin\Discount;

use App\Models\Admin\Discount\Discount;
use Illuminate\Validation\ValidationException;

class DiscountRepository
{
    /**
     * Get all discounts with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllDiscounts(array $filters = [])
    {
        $query = Discount::query();

        // Apply status filter
        if (isset($filters['is_active']) && !empty($filters['is_active'])) {
            $statusValues = is_array($filters['is_active']) ? $filters['is_active'] : [$filters['is_active']];
            $statusBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $statusValues);
            $query->whereIn('is_active', $statusBooleans);
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
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->ordered()->get();
    }

    /**
     * Get a discount by ID.
     *
     * @param int $id
     * @return Discount
     */
    public function getDiscountById($id)
    {
        return Discount::findOrFail($id);
    }

    /**
     * Get a discount by code.
     *
     * @param string $code
     * @return Discount|null
     */
    public function getDiscountByCode($code)
    {
        return Discount::where('code', strtoupper($code))->first();
    }

    /**
     * Create a new discount.
     *
     * @param array $data
     * @return Discount
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $discountData = [
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'percentage',
            'value' => $data['value'],
            'minimum_purchase' => $data['minimum_purchase'] ?? null,
            'maximum_discount' => $data['maximum_discount'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'total_usage_limit' => $data['total_usage_limit'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'] ?? true,
            'is_first_time_only' => $data['is_first_time_only'] ?? false,
            'free_shipping' => $data['free_shipping'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle JSON fields
        if (isset($data['applicable_categories']) && is_array($data['applicable_categories'])) {
            $discountData['applicable_categories'] = array_filter($data['applicable_categories']);
        }
        if (isset($data['applicable_products']) && is_array($data['applicable_products'])) {
            $discountData['applicable_products'] = array_filter($data['applicable_products']);
        }
        if (isset($data['excluded_categories']) && is_array($data['excluded_categories'])) {
            $discountData['excluded_categories'] = array_filter($data['excluded_categories']);
        }
        if (isset($data['excluded_products']) && is_array($data['excluded_products'])) {
            $discountData['excluded_products'] = array_filter($data['excluded_products']);
        }

        return Discount::create($discountData);
    }

    /**
     * Update a discount.
     *
     * @param int $id
     * @param array $data
     * @return Discount
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $discount = Discount::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? $discount->type,
            'value' => $data['value'],
            'minimum_purchase' => $data['minimum_purchase'] ?? null,
            'maximum_discount' => $data['maximum_discount'] ?? null,
            'usage_limit_per_user' => $data['usage_limit_per_user'] ?? null,
            'total_usage_limit' => $data['total_usage_limit'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'] ?? $discount->is_active,
            'is_first_time_only' => $data['is_first_time_only'] ?? $discount->is_first_time_only,
            'free_shipping' => $data['free_shipping'] ?? $discount->free_shipping,
            'sort_order' => $data['sort_order'] ?? $discount->sort_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Update code only if explicitly provided
        if (isset($data['code']) && $data['code'] !== $discount->code) {
            $updateData['code'] = $data['code'];
        }

        // Handle JSON fields
        if (isset($data['applicable_categories'])) {
            $updateData['applicable_categories'] = is_array($data['applicable_categories']) 
                ? array_filter($data['applicable_categories']) 
                : null;
        }
        if (isset($data['applicable_products'])) {
            $updateData['applicable_products'] = is_array($data['applicable_products']) 
                ? array_filter($data['applicable_products']) 
                : null;
        }
        if (isset($data['excluded_categories'])) {
            $updateData['excluded_categories'] = is_array($data['excluded_categories']) 
                ? array_filter($data['excluded_categories']) 
                : null;
        }
        if (isset($data['excluded_products'])) {
            $updateData['excluded_products'] = is_array($data['excluded_products']) 
                ? array_filter($data['excluded_products']) 
                : null;
        }

        $discount->update($updateData);
        return $discount->fresh();
    }

    /**
     * Delete a discount.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $discount = Discount::findOrFail($id);
        return $discount->delete();
    }

    /**
     * Bulk delete discounts.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $discounts = Discount::whereIn('id', $ids)->get();

        foreach ($discounts as $discount) {
            $discount->delete();
        }

        return true;
    }

    /**
     * Toggle discount active status.
     *
     * @param int $id
     * @return Discount
     */
    public function toggleStatus($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);
        return $discount;
    }

    /**
     * Increment usage count.
     *
     * @param int $id
     * @return Discount
     */
    public function incrementUsage($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->increment('used_count');
        return $discount;
    }

    /**
     * Update sort order for multiple discounts.
     *
     * @param array $sortData Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder(array $sortData)
    {
        foreach ($sortData as $id => $sortOrder) {
            Discount::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
        return true;
    }

    /**
     * Get discounts for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDiscountsForDropdown()
    {
        return Discount::active()
            ->valid()
            ->ordered()
            ->get()
            ->map(function ($discount) {
                return [
                    'id' => $discount->id,
                    'name' => $discount->name,
                    'code' => $discount->code,
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
            'total' => Discount::count(),
            'active' => Discount::active()->count(),
            'valid' => Discount::valid()->count(),
        ];
    }

    /**
     * Validate discount data.
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
            'code' => 'nullable|string|max:50|unique:discounts,code',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'minimum_purchase' => 'nullable|numeric|min:0',
            'maximum_discount' => 'nullable|numeric|min:0',
            'usage_limit_per_user' => 'nullable|integer|min:1',
            'total_usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
            'is_first_time_only' => 'boolean',
            'free_shipping' => 'boolean',
            'applicable_categories' => 'nullable|array',
            'applicable_products' => 'nullable|array',
            'excluded_categories' => 'nullable|array',
            'excluded_products' => 'nullable|array',
            'sort_order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ];

        if ($isUpdate && $id) {
            $rules['code'] = 'nullable|string|max:50|unique:discounts,code,' . $id;
        }

        // Additional validation for percentage type
        if (isset($data['type']) && $data['type'] === 'percentage') {
            $rules['value'] = 'required|numeric|min:0|max:100';
        }

        return validator($data, $rules)->validate();
    }
}

