<?php

namespace App\Repositories\Api;

use App\Models\Admin\Brand\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository
{
    /**
     * Get all active brands with pagination support.
     *
     * @param array $filters
     * @return Collection
     */
    public function getBrands(array $filters = []): Collection
    {
        $query = Brand::where('is_active', true); // Only show active brands

        // Apply featured filter
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $isFeatured = filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_featured', $isFeatured);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->ordered()->get();
    }

    /**
     * Get a single brand by ID or slug.
     *
     * @param string|int $id
     * @return Brand|null
     */
    public function getBrandByIdOrSlug($id): ?Brand
    {
        return Brand::where(function ($query) use ($id) {
            $query->where('id', $id)
                  ->orWhere('slug', $id);
        })
        ->where('is_active', true)
        ->first();
    }

    /**
     * Get featured brands.
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeaturedBrands(int $limit = 10): Collection
    {
        return Brand::where('is_active', true)
            ->where('is_featured', true)
            ->ordered()
            ->limit($limit)
            ->get();
    }

    /**
     * Get brands for dropdown (public API).
     *
     * @return Collection
     */
    public function getBrandsForDropdown(): Collection
    {
        return Brand::where('is_active', true)
            ->ordered()
            ->get()
            ->map(function ($brand) {
                return [
                    'id' => $brand->id,
                    'name' => $brand->name,
                    'slug' => $brand->slug,
                ];
            });
    }
}
