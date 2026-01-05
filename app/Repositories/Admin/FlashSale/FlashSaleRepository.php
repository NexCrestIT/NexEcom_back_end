<?php

namespace App\Repositories\Admin\FlashSale;

use App\Models\Admin\FlashSale\FlashSale;
use App\Models\Admin\Product\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FlashSaleRepository
{
    /**
     * Get all flash sales with ordering and filtering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllFlashSales(array $filters = [])
    {
        $query = FlashSale::query();

        // Apply status filter
        if (isset($filters['is_active']) && !empty($filters['is_active'])) {
            $statusValues = is_array($filters['is_active']) ? $filters['is_active'] : [$filters['is_active']];
            $statusBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $statusValues);
            $query->whereIn('is_active', $statusBooleans);
        }

        // Apply featured filter
        if (isset($filters['is_featured']) && !empty($filters['is_featured'])) {
            $featuredValues = is_array($filters['is_featured']) ? $filters['is_featured'] : [$filters['is_featured']];
            $featuredBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $featuredValues);
            $query->whereIn('is_featured', $featuredBooleans);
        }

        // Apply status filter (ongoing, upcoming, expired)
        if (isset($filters['status']) && !empty($filters['status'])) {
            $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
            $now = now();
            
            $query->where(function($q) use ($statuses, $now) {
                if (in_array('ongoing', $statuses)) {
                    $q->orWhere(function($subQ) use ($now) {
                        $subQ->where('is_active', true)
                            ->where('start_date', '<=', $now)
                            ->where('end_date', '>=', $now);
                    });
                }
                if (in_array('upcoming', $statuses)) {
                    $q->orWhere('start_date', '>', $now);
                }
                if (in_array('expired', $statuses)) {
                    $q->orWhere('end_date', '<', $now);
                }
            });
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
     * Get a flash sale by ID.
     *
     * @param int $id
     * @return FlashSale
     */
    public function getFlashSaleById($id)
    {
        return FlashSale::with('products')->findOrFail($id);
    }

    /**
     * Create a new flash sale.
     *
     * @param array $data
     * @return FlashSale
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $flashSaleData = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'discount_type' => $data['discount_type'] ?? 'percentage',
            'discount_value' => $data['discount_value'] ?? null,
            'max_products' => $data['max_products'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle banner image upload
        if (isset($data['banner_image']) && $data['banner_image']) {
            $flashSaleData['banner_image'] = $this->handleBannerImage($data['banner_image']);
        }

        $flashSale = FlashSale::create($flashSaleData);

        // Sync products if provided
        if (isset($data['products']) && is_array($data['products'])) {
            $this->syncProducts($flashSale, $data['products']);
        }

        return $flashSale->load('products');
    }

    /**
     * Update a flash sale.
     *
     * @param int $id
     * @param array $data
     * @return FlashSale
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $flashSale = FlashSale::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'discount_type' => $data['discount_type'] ?? $flashSale->discount_type,
            'discount_value' => $data['discount_value'] ?? null,
            'max_products' => $data['max_products'] ?? null,
            'is_active' => $data['is_active'] ?? $flashSale->is_active,
            'is_featured' => $data['is_featured'] ?? $flashSale->is_featured,
            'sort_order' => $data['sort_order'] ?? $flashSale->sort_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle banner image upload or removal
        if (isset($data['remove_banner_image']) && $data['remove_banner_image']) {
            if ($flashSale->banner_image) {
                Storage::disk('public')->delete($flashSale->banner_image);
            }
            $updateData['banner_image'] = null;
        } elseif (isset($data['banner_image']) && $data['banner_image']) {
            // Delete old banner if exists
            if ($flashSale->banner_image) {
                Storage::disk('public')->delete($flashSale->banner_image);
            }
            $updateData['banner_image'] = $this->handleBannerImage($data['banner_image']);
        }

        $flashSale->update($updateData);

        // Sync products if provided
        if (isset($data['products'])) {
            $this->syncProducts($flashSale, $data['products']);
        }

        return $flashSale->load('products');
    }

    /**
     * Delete a flash sale.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $flashSale = FlashSale::findOrFail($id);
        
        // Delete banner image
        if ($flashSale->banner_image) {
            Storage::disk('public')->delete($flashSale->banner_image);
        }

        return $flashSale->delete();
    }

    /**
     * Bulk delete flash sales.
     *
     * @param array $ids
     * @return int
     */
    public function bulkDelete(array $ids)
    {
        $flashSales = FlashSale::whereIn('id', $ids)->get();
        
        foreach ($flashSales as $flashSale) {
            if ($flashSale->banner_image) {
                Storage::disk('public')->delete($flashSale->banner_image);
            }
        }

        return FlashSale::whereIn('id', $ids)->delete();
    }

    /**
     * Toggle active status.
     *
     * @param int $id
     * @return FlashSale
     */
    public function toggleStatus($id)
    {
        $flashSale = FlashSale::findOrFail($id);
        $flashSale->is_active = !$flashSale->is_active;
        $flashSale->save();

        return $flashSale;
    }

    /**
     * Toggle featured status.
     *
     * @param int $id
     * @return FlashSale
     */
    public function toggleFeatured($id)
    {
        $flashSale = FlashSale::findOrFail($id);
        $flashSale->is_featured = !$flashSale->is_featured;
        $flashSale->save();

        return $flashSale;
    }

    /**
     * Update sort order.
     *
     * @param int $id
     * @param int $sortOrder
     * @return FlashSale
     */
    public function updateSortOrder($id, $sortOrder)
    {
        $flashSale = FlashSale::findOrFail($id);
        $flashSale->sort_order = $sortOrder;
        $flashSale->save();

        return $flashSale;
    }

    /**
     * Sync products to flash sale.
     *
     * @param FlashSale $flashSale
     * @param array $products
     * @return void
     */
    protected function syncProducts(FlashSale $flashSale, array $products)
    {
        $syncData = [];
        
        foreach ($products as $product) {
            if (isset($product['product_id']) && $product['product_id']) {
                $syncData[$product['product_id']] = [
                    'discount_type' => $product['discount_type'] ?? null,
                    'discount_value' => $product['discount_value'] ?? null,
                    'sort_order' => $product['sort_order'] ?? 0,
                ];
            }
        }

        $flashSale->products()->sync($syncData);
    }

    /**
     * Handle banner image upload.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string
     */
    protected function handleBannerImage($file)
    {
        $path = $file->store('flash_sales/banners', 'public');
        return $path;
    }

    /**
     * Get flash sales for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFlashSalesForDropdown()
    {
        return FlashSale::active()
            ->ordered()
            ->select('id', 'name')
            ->get();
    }

    /**
     * Get statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total' => FlashSale::count(),
            'active' => FlashSale::active()->count(),
            'featured' => FlashSale::featured()->count(),
            'ongoing' => FlashSale::ongoing()->count(),
            'upcoming' => FlashSale::upcoming()->count(),
            'expired' => FlashSale::expired()->count(),
        ];
    }

    /**
     * Validate flash sale data.
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
            'slug' => 'nullable|string|max:255|unique:flash_sales,slug',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'nullable|numeric|min:0',
            'max_products' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.discount_type' => 'nullable|in:percentage,fixed',
            'products.*.discount_value' => 'nullable|numeric|min:0',
            'products.*.sort_order' => 'nullable|integer|min:0',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:flash_sales,slug,' . $id;
        }

        // Additional validation for percentage type
        if (isset($data['discount_type']) && $data['discount_type'] === 'percentage') {
            if (isset($data['discount_value'])) {
                $rules['discount_value'] = 'nullable|numeric|min:0|max:100';
            }
        }

        return validator($data, $rules)->validate();
    }
}

