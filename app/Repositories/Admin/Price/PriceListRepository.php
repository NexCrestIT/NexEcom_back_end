<?php

namespace App\Repositories\Admin\Price;

use App\Models\Admin\Price\PriceList;
use App\Models\Admin\Price\PriceHistory;
use Illuminate\Validation\ValidationException;

class PriceListRepository
{
    /**
     * Get all price lists with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllPriceLists(array $filters = [])
    {
        $query = PriceList::with('priceRules');

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

        // Apply default filter
        if (isset($filters['is_default']) && !empty($filters['is_default'])) {
            $defaultValues = is_array($filters['is_default']) ? $filters['is_default'] : [$filters['is_default']];
            $defaultBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $defaultValues);
            $query->whereIn('is_default', $defaultBooleans);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->ordered()->get();
    }

    /**
     * Get a price list by ID.
     *
     * @param int $id
     * @return PriceList
     */
    public function getPriceListById($id)
    {
        return PriceList::with(['priceRules.product', 'priceHistory'])->findOrFail($id);
    }

    /**
     * Get default price list.
     *
     * @return PriceList|null
     */
    public function getDefaultPriceList()
    {
        return PriceList::default()->active()->first();
    }

    /**
     * Create a new price list.
     *
     * @param array $data
     * @return PriceList
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $priceListData = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'custom',
            'is_active' => $data['is_active'] ?? true,
            'is_default' => $data['is_default'] ?? false,
            'priority' => $data['priority'] ?? 0,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_to' => $data['valid_to'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle JSON fields
        if (isset($data['applicable_categories']) && is_array($data['applicable_categories'])) {
            $priceListData['applicable_categories'] = array_filter($data['applicable_categories']);
        }
        if (isset($data['applicable_products']) && is_array($data['applicable_products'])) {
            $priceListData['applicable_products'] = array_filter($data['applicable_products']);
        }
        if (isset($data['applicable_customer_groups']) && is_array($data['applicable_customer_groups'])) {
            $priceListData['applicable_customer_groups'] = array_filter($data['applicable_customer_groups']);
        }

        return PriceList::create($priceListData);
    }

    /**
     * Update a price list.
     *
     * @param int $id
     * @param array $data
     * @return PriceList
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $priceList = PriceList::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? $priceList->type,
            'is_active' => $data['is_active'] ?? $priceList->is_active,
            'is_default' => $data['is_default'] ?? $priceList->is_default,
            'priority' => $data['priority'] ?? $priceList->priority,
            'valid_from' => $data['valid_from'] ?? $priceList->valid_from,
            'valid_to' => $data['valid_to'] ?? $priceList->valid_to,
            'currency' => $data['currency'] ?? $priceList->currency,
            'sort_order' => $data['sort_order'] ?? $priceList->sort_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

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
        if (isset($data['applicable_customer_groups'])) {
            $updateData['applicable_customer_groups'] = is_array($data['applicable_customer_groups']) 
                ? array_filter($data['applicable_customer_groups']) 
                : null;
        }

        $priceList->update($updateData);
        return $priceList->fresh(['priceRules.product', 'priceHistory']);
    }

    /**
     * Delete a price list.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $priceList = PriceList::findOrFail($id);
        return $priceList->delete();
    }

    /**
     * Bulk delete price lists.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids)
    {
        return PriceList::whereIn('id', $ids)->delete();
    }

    /**
     * Toggle active status.
     *
     * @param int $id
     * @return PriceList
     */
    public function toggleStatus($id)
    {
        $priceList = PriceList::findOrFail($id);
        $priceList->is_active = !$priceList->is_active;
        $priceList->save();
        return $priceList;
    }

    /**
     * Set as default price list.
     *
     * @param int $id
     * @return PriceList
     */
    public function setAsDefault($id)
    {
        $priceList = PriceList::findOrFail($id);
        
        // Unset other default price lists
        PriceList::where('id', '!=', $id)->update(['is_default' => false]);
        
        $priceList->is_default = true;
        $priceList->save();
        return $priceList;
    }

    /**
     * Update sort order.
     *
     * @param array $sortOrder Array of ['id' => sort_order]
     * @return void
     */
    public function updateSortOrder(array $sortOrder)
    {
        foreach ($sortOrder as $id => $order) {
            PriceList::where('id', $id)->update(['sort_order' => $order]);
        }
    }

    /**
     * Get price lists for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPriceListsForDropdown()
    {
        return PriceList::active()
            ->ordered()
            ->get()
            ->map(function ($priceList) {
                return [
                    'id' => $priceList->id,
                    'name' => $priceList->name,
                    'type' => $priceList->type,
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
            'total' => PriceList::count(),
            'active' => PriceList::active()->count(),
            'inactive' => PriceList::where('is_active', false)->count(),
            'default' => PriceList::default()->count(),
            'wholesale' => PriceList::ofType('wholesale')->count(),
            'retail' => PriceList::ofType('retail')->count(),
            'custom' => PriceList::ofType('custom')->count(),
            'promotional' => PriceList::ofType('promotional')->count(),
        ];
    }

    /**
     * Validate price list data.
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
            'slug' => 'nullable|string|max:255|unique:price_lists,slug' . ($isUpdate ? ',' . $id : ''),
            'description' => 'nullable|string',
            'type' => 'required|in:wholesale,retail,custom,promotional',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'priority' => 'nullable|integer|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'currency' => 'nullable|string|size:3',
            'sort_order' => 'nullable|integer',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'applicable_categories' => 'nullable|array',
            'applicable_categories.*' => 'integer|exists:categories,id',
            'applicable_products' => 'nullable|array',
            'applicable_products.*' => 'integer|exists:products,id',
            'applicable_customer_groups' => 'nullable|array',
        ];

        return validator($data, $rules)->validate();
    }
}

