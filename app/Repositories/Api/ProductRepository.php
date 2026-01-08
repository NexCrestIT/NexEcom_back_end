<?php

namespace App\Repositories\Api;

use App\Models\Admin\Product\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository
{
    /**
     * Get paginated products for public API.
     */
    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::with(['category', 'brand', 'collection'])
            ->where('is_active', true); // Only show active products

        // Apply category filter
        if (isset($filters['category_id']) && ! empty($filters['category_id'])) {
            $categoryIds = is_array($filters['category_id']) ? $filters['category_id'] : [$filters['category_id']];
            $query->whereIn('category_id', $categoryIds);
        }

        // Apply brand filter
        if (isset($filters['brand_id']) && ! empty($filters['brand_id'])) {
            $brandIds = is_array($filters['brand_id']) ? $filters['brand_id'] : [$filters['brand_id']];
            $query->whereIn('brand_id', $brandIds);
        }

        // Apply collection filter
        if (isset($filters['collection_id']) && ! empty($filters['collection_id'])) {
            $collectionIds = is_array($filters['collection_id']) ? $filters['collection_id'] : [$filters['collection_id']];
            $query->whereIn('collection_id', $collectionIds);
        }

        // Apply gender filter
        if (isset($filters['gender_id']) && ! empty($filters['gender_id'])) {
            $genderIds = is_array($filters['gender_id']) ? $filters['gender_id'] : [$filters['gender_id']];
            $query->whereIn('gender_id', $genderIds);
        }

        // Apply scent family filter
        if (isset($filters['scent_family_id']) && ! empty($filters['scent_family_id'])) {
            $scentFamilyIds = is_array($filters['scent_family_id']) ? $filters['scent_family_id'] : [$filters['scent_family_id']];
            $query->whereIn('scent_family_id', $scentFamilyIds);
        }

        // Apply featured filter
        if (isset($filters['is_featured']) && $filters['is_featured'] !== '') {
            $isFeatured = filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_featured', $isFeatured);
        }

        // Apply new products filter
        if (isset($filters['is_new']) && $filters['is_new'] !== '') {
            $isNew = filter_var($filters['is_new'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_new', $isNew);
        }

        // Apply bestseller filter
        if (isset($filters['is_bestseller']) && $filters['is_bestseller'] !== '') {
            $isBestseller = filter_var($filters['is_bestseller'], FILTER_VALIDATE_BOOLEAN);
            $query->where('is_bestseller', $isBestseller);
        }

        // Apply price range filter
        if (isset($filters['min_price']) && $filters['min_price'] !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply search filter
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'sort_order';
        $sortOrder = $filters['sort_order'] ?? 'asc';

        $allowedSortFields = ['name', 'price', 'created_at', 'sort_order', 'rating', 'sold_count'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('sort_order', 'asc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single product by ID or slug.
     */
    public function getProductByIdOrSlug(string $identifier): ?Product
    {
        $product = Product::with(['category', 'brand', 'collection', 'gender', 'scentFamily', 'tags', 'labels'])
            ->where('is_active', true)
            ->where(function ($query) use ($identifier) {
                if (is_numeric($identifier)) {
                    $query->where('id', $identifier);
                } else {
                    $query->where('slug', $identifier);
                }
            })
            ->first();

        if ($product) {
            // Increment view count
            $product->increment('view_count');
        }

        return $product;
    }

    /**
     * Get featured products.
     */
    public function getFeaturedProducts(int $limit = 10): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get new products.
     */
    public function getNewProducts(int $limit = 10): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('is_new', true)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get bestseller products.
     */
    public function getBestsellerProducts(int $limit = 10): Collection
    {
        return Product::with(['category', 'brand'])
            ->where('is_active', true)
            ->where('is_bestseller', true)
            ->orderBy('sold_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
