<?php

namespace App\Repositories\Admin\Brand;

use App\Models\Admin\Brand\Brand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BrandRepository
{
    /**
     * Get all brands with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBrands(array $filters = [])
    {
        $query = Brand::query();

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
     * Get a brand by ID.
     *
     * @param int $id
     * @return Brand
     */
    public function getBrandById($id)
    {
        return Brand::findOrFail($id);
    }

    /**
     * Create a new brand.
     *
     * @param array $data
     * @return Brand
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $brandData = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle logo upload
        if (isset($data['logo']) && $data['logo']) {
            $brandData['logo'] = $this->handleLogoUpload($data['logo']);
        }

        return Brand::create($brandData);
    }

    /**
     * Update a brand.
     *
     * @param int $id
     * @param array $data
     * @return Brand
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $brand = Brand::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'is_active' => $data['is_active'] ?? $brand->is_active,
            'is_featured' => $data['is_featured'] ?? $brand->is_featured,
            'sort_order' => $data['sort_order'] ?? $brand->sort_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $brand->slug) {
            $updateData['slug'] = $data['slug'];
        }

        // Handle logo upload
        if (isset($data['logo']) && $data['logo']) {
            // Delete old logo if exists
            if ($brand->logo) {
                $this->deleteLogo($brand->logo);
            }
            $updateData['logo'] = $this->handleLogoUpload($data['logo']);
        }

        $brand->update($updateData);
        return $brand->fresh();
    }

    /**
     * Delete a brand.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $brand = Brand::findOrFail($id);
        
        // Delete logo if exists
        if ($brand->logo) {
            $this->deleteLogo($brand->logo);
        }
        
        return $brand->delete();
    }

    /**
     * Bulk delete brands.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $brands = Brand::whereIn('id', $ids)->get();

        foreach ($brands as $brand) {
            // Delete logo if exists
            if ($brand->logo) {
                $this->deleteLogo($brand->logo);
            }
            $brand->delete();
        }

        return true;
    }

    /**
     * Toggle brand active status.
     *
     * @param int $id
     * @return Brand
     */
    public function toggleStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['is_active' => !$brand->is_active]);
        return $brand;
    }

    /**
     * Toggle brand featured status.
     *
     * @param int $id
     * @return Brand
     */
    public function toggleFeatured($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['is_featured' => !$brand->is_featured]);
        return $brand;
    }

    /**
     * Update sort order for multiple brands.
     *
     * @param array $sortData Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder(array $sortData)
    {
        foreach ($sortData as $id => $sortOrder) {
            Brand::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
        return true;
    }

    /**
     * Get brands for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBrandsForDropdown()
    {
        return Brand::active()
            ->ordered()
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
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
            'total' => Brand::count(),
            'active' => Brand::active()->count(),
            'featured' => Brand::featured()->count(),
        ];
    }

    /**
     * Handle logo upload.
     *
     * @param mixed $logo
     * @return string
     */
    protected function handleLogoUpload($logo)
    {
        if (is_string($logo) && filter_var($logo, FILTER_VALIDATE_URL)) {
            // If it's a URL, return as is
            return $logo;
        }

        if (is_file($logo)) {
            $path = $logo->store('brands', 'public');
            return $path;
        }

        return $logo;
    }

    /**
     * Delete logo file.
     *
     * @param string $logoPath
     * @return void
     */
    protected function deleteLogo($logoPath)
    {
        if (filter_var($logoPath, FILTER_VALIDATE_URL)) {
            return; // Don't delete external URLs
        }

        if (Storage::disk('public')->exists($logoPath)) {
            Storage::disk('public')->delete($logoPath);
        }
    }

    /**
     * Validate brand data.
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
            'slug' => 'nullable|string|max:255|unique:brands,slug',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'website' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:brands,slug,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}

