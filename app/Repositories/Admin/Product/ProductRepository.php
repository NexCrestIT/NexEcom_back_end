<?php

namespace App\Repositories\Admin\Product;

use App\Models\Admin\Product\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductRepository
{
    /**
     * Get all products with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllProducts(array $filters = [])
    {
        $query = Product::with(['category', 'brand', 'collection', 'tags', 'labels']);

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

        // Apply category filter
        if (isset($filters['category_id']) && !empty($filters['category_id'])) {
            $categoryIds = is_array($filters['category_id']) ? $filters['category_id'] : [$filters['category_id']];
            $query->whereIn('category_id', $categoryIds);
        }

        // Apply brand filter
        if (isset($filters['brand_id']) && !empty($filters['brand_id'])) {
            $brandIds = is_array($filters['brand_id']) ? $filters['brand_id'] : [$filters['brand_id']];
            $query->whereIn('brand_id', $brandIds);
        }

        // Apply collection filter
        if (isset($filters['collection_id']) && !empty($filters['collection_id'])) {
            $collectionIds = is_array($filters['collection_id']) ? $filters['collection_id'] : [$filters['collection_id']];
            $query->whereIn('collection_id', $collectionIds);
        }

        // Apply stock filter
        if (isset($filters['stock_status']) && !empty($filters['stock_status'])) {
            $stockStatuses = is_array($filters['stock_status']) ? $filters['stock_status'] : [$filters['stock_status']];
            foreach ($stockStatuses as $status) {
                if ($status === 'in_stock') {
                    $query->inStock();
                } elseif ($status === 'out_of_stock') {
                    $query->where(function($q) {
                        $q->where('track_inventory', true)
                          ->where('allow_backorder', false)
                          ->where('stock_quantity', '<=', 0);
                    });
                } elseif ($status === 'low_stock') {
                    $query->lowStock();
                }
            }
        }

        // Apply price range filter
        if (isset($filters['min_price']) && $filters['min_price'] !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if (isset($filters['max_price']) && $filters['max_price'] !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('short_description', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->ordered()->get();
    }

    /**
     * Get a product by ID.
     *
     * @param int $id
     * @return Product
     */
    public function getProductById($id)
    {
        return Product::with(['category', 'brand', 'collection', 'tags', 'labels', 'attributes.attributeValue', 'discounts'])
            ->findOrFail($id);
    }

    /**
     * Create a new product.
     *
     * @param array $data
     * @return Product
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $productData = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'sku' => $data['sku'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'specifications' => $data['specifications'] ?? null,
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'cost_price' => $data['cost_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'track_inventory' => $data['track_inventory'] ?? true,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
            'allow_backorder' => $data['allow_backorder'] ?? false,
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'collection_id' => $data['collection_id'] ?? null,
            'gender_id' => $data['gender_id'] ?? null,
            'scent_family_id' => $data['scent_family_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'is_new' => $data['is_new'] ?? false,
            'is_bestseller' => $data['is_bestseller'] ?? false,
            'is_digital' => $data['is_digital'] ?? false,
            'is_virtual' => $data['is_virtual'] ?? false,
            'weight' => $data['weight'] ?? null,
            'weight_unit' => $data['weight_unit'] ?? 'kg',
            'length' => $data['length'] ?? null,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'dimension_unit' => $data['dimension_unit'] ?? 'cm',
            'taxable' => $data['taxable'] ?? true,
            'tax_rate' => $data['tax_rate'] ?? null,
            'requires_shipping' => $data['requires_shipping'] ?? true,
            'shipping_weight' => $data['shipping_weight'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ];

        // Handle main image upload
        if (isset($data['main_image']) && $data['main_image']) {
            $productData['main_image'] = $this->handleImageUpload($data['main_image']);
        }

        // Handle gallery images upload
        if (isset($data['gallery_images']) && is_array($data['gallery_images'])) {
            $galleryPaths = [];
            foreach ($data['gallery_images'] as $image) {
                if ($image) {
                    $galleryPaths[] = $this->handleImageUpload($image);
                }
            }
            $productData['gallery_images'] = !empty($galleryPaths) ? $galleryPaths : null;
        }

        $product = Product::create($productData);

        // Sync relationships
        $this->syncRelationships($product, $data);

        return $product->fresh(['category', 'brand', 'collection', 'tags', 'labels', 'attributes', 'discounts']);
    }

    /**
     * Update a product.
     *
     * @param int $id
     * @param array $data
     * @return Product
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $product = Product::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'specifications' => $data['specifications'] ?? null,
            'price' => $data['price'],
            'compare_at_price' => $data['compare_at_price'] ?? null,
            'cost_price' => $data['cost_price'] ?? null,
            'stock_quantity' => $data['stock_quantity'] ?? $product->stock_quantity,
            'track_inventory' => $data['track_inventory'] ?? $product->track_inventory,
            'low_stock_threshold' => $data['low_stock_threshold'] ?? null,
            'allow_backorder' => $data['allow_backorder'] ?? $product->allow_backorder,
            'category_id' => $data['category_id'] ?? null,
            'brand_id' => $data['brand_id'] ?? null,
            'collection_id' => $data['collection_id'] ?? null,
            'gender_id' => $data['gender_id'] ?? null,
            'scent_family_id' => $data['scent_family_id'] ?? null,
            'is_active' => $data['is_active'] ?? $product->is_active,
            'is_featured' => $data['is_featured'] ?? $product->is_featured,
            'is_new' => $data['is_new'] ?? $product->is_new,
            'is_bestseller' => $data['is_bestseller'] ?? $product->is_bestseller,
            'is_digital' => $data['is_digital'] ?? $product->is_digital,
            'is_virtual' => $data['is_virtual'] ?? $product->is_virtual,
            'weight' => $data['weight'] ?? null,
            'weight_unit' => $data['weight_unit'] ?? $product->weight_unit,
            'length' => $data['length'] ?? null,
            'width' => $data['width'] ?? null,
            'height' => $data['height'] ?? null,
            'dimension_unit' => $data['dimension_unit'] ?? $product->dimension_unit,
            'taxable' => $data['taxable'] ?? $product->taxable,
            'tax_rate' => $data['tax_rate'] ?? null,
            'requires_shipping' => $data['requires_shipping'] ?? $product->requires_shipping,
            'shipping_weight' => $data['shipping_weight'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
            'sort_order' => $data['sort_order'] ?? $product->sort_order,
        ];

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $product->slug) {
            $updateData['slug'] = $data['slug'];
        }

        // Update SKU only if explicitly provided
        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            $updateData['sku'] = $data['sku'];
        }

        // Handle main image upload
        if (isset($data['main_image']) && $data['main_image']) {
            // Delete old image if exists
            if ($product->main_image) {
                $this->deleteImage($product->main_image);
            }
            $updateData['main_image'] = $this->handleImageUpload($data['main_image']);
        }

        // Handle gallery images upload
        if (isset($data['gallery_images']) && is_array($data['gallery_images'])) {
            // Delete old gallery images
            if ($product->gallery_images) {
                foreach ($product->gallery_images as $oldImage) {
                    $this->deleteImage($oldImage);
                }
            }
            $galleryPaths = [];
            foreach ($data['gallery_images'] as $image) {
                if ($image) {
                    $galleryPaths[] = $this->handleImageUpload($image);
                }
            }
            $updateData['gallery_images'] = !empty($galleryPaths) ? $galleryPaths : null;
        }

        $product->update($updateData);

        // Sync relationships
        $this->syncRelationships($product, $data);

        return $product->fresh(['category', 'brand', 'collection', 'tags', 'labels', 'attributes', 'discounts']);
    }

    /**
     * Sync product relationships.
     *
     * @param Product $product
     * @param array $data
     * @return void
     */
    protected function syncRelationships(Product $product, array $data)
    {
        // Sync tags
        if (isset($data['tags']) && is_array($data['tags'])) {
            $product->tags()->sync($data['tags']);
        }

        // Sync labels
        if (isset($data['labels']) && is_array($data['labels'])) {
            $product->labels()->sync($data['labels']);
        }

        // Sync discounts
        if (isset($data['discounts']) && is_array($data['discounts'])) {
            $product->discounts()->sync($data['discounts']);
        }

        // Sync attributes
        if (isset($data['attributes']) && is_array($data['attributes'])) {
            $syncData = [];
            foreach ($data['attributes'] as $attributeData) {
                $syncData[$attributeData['attribute_id']] = [
                    'attribute_value_id' => $attributeData['attribute_value_id'] ?? null,
                    'value' => $attributeData['value'] ?? null,
                    'sort_order' => $attributeData['sort_order'] ?? 0,
                ];
            }
            $product->attributes()->sync($syncData);
        }
    }

    /**
     * Delete a product.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $product = Product::findOrFail($id);
        
        // Delete images
        if ($product->main_image) {
            $this->deleteImage($product->main_image);
        }
        if ($product->gallery_images) {
            foreach ($product->gallery_images as $image) {
                $this->deleteImage($image);
            }
        }
        
        return $product->delete();
    }

    /**
     * Bulk delete products.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $products = Product::whereIn('id', $ids)->get();

        foreach ($products as $product) {
            // Delete images
            if ($product->main_image) {
                $this->deleteImage($product->main_image);
            }
            if ($product->gallery_images) {
                foreach ($product->gallery_images as $image) {
                    $this->deleteImage($image);
                }
            }
            $product->delete();
        }

        return true;
    }

    /**
     * Toggle product active status.
     *
     * @param int $id
     * @return Product
     */
    public function toggleStatus($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        return $product;
    }

    /**
     * Toggle product featured status.
     *
     * @param int $id
     * @return Product
     */
    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);
        return $product;
    }

    /**
     * Update stock quantity.
     *
     * @param int $id
     * @param int $quantity
     * @return Product
     */
    public function updateStock($id, $quantity)
    {
        $product = Product::findOrFail($id);
        $product->update(['stock_quantity' => $quantity]);
        return $product;
    }

    /**
     * Update sort order for multiple products.
     *
     * @param array $sortData Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder(array $sortData)
    {
        foreach ($sortData as $id => $sortOrder) {
            Product::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
        return true;
    }

    /**
     * Get products for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getProductsForDropdown()
    {
        return Product::active()
            ->ordered()
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
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
            'total' => Product::count(),
            'active' => Product::active()->count(),
            'featured' => Product::featured()->count(),
            'out_of_stock' => Product::where('track_inventory', true)
                ->where('allow_backorder', false)
                ->where('stock_quantity', '<=', 0)
                ->count(),
            'low_stock' => Product::lowStock()->count(),
        ];
    }

    /**
     * Handle image upload.
     *
     * @param mixed $image
     * @return string
     */
    protected function handleImageUpload($image)
    {
        if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        if (is_file($image)) {
            $path = $image->store('products', 'public');
            return $path;
        }

        return $image;
    }

    /**
     * Delete image file.
     *
     * @param string $imagePath
     * @return void
     */
    protected function deleteImage($imagePath)
    {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Validate product data.
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
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'specifications' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'track_inventory' => 'boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'allow_backorder' => 'boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'collection_id' => 'nullable|exists:collections,id',
            'gender_id' => 'nullable|exists:genders,id',
            'scent_family_id' => 'nullable|exists:scent_families,id',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'is_new' => 'boolean',
            'is_bestseller' => 'boolean',
            'is_digital' => 'boolean',
            'is_virtual' => 'boolean',
            'weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|in:kg,g,lb,oz',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'dimension_unit' => 'nullable|in:cm,m,in,ft',
            'taxable' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'requires_shipping' => 'boolean',
            'shipping_weight' => 'nullable|numeric|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
            'sort_order' => 'integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'labels' => 'nullable|array',
            'labels.*' => 'exists:labels,id',
            'discounts' => 'nullable|array',
            'discounts.*' => 'exists:discounts,id',
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'required|exists:attributes,id',
            'attributes.*.attribute_value_id' => 'nullable|exists:attribute_values,id',
            'attributes.*.value' => 'nullable|string',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:products,slug,' . $id;
            $rules['sku'] = 'nullable|string|max:100|unique:products,sku,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}

