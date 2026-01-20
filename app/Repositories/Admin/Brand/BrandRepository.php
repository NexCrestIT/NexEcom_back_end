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

        if (isset($filters['is_active']) && !empty($filters['is_active'])) {
            $statusValues = is_array($filters['is_active']) ? $filters['is_active'] : [$filters['is_active']];
            $statusBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $statusValues);
            $query->whereIn('is_active', $statusBooleans);
        }

        if (isset($filters['is_featured']) && !empty($filters['is_featured'])) {
            $featuredValues = is_array($filters['is_featured']) ? $filters['is_featured'] : [$filters['is_featured']];
            $featuredBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $featuredValues);
            $query->whereIn('is_featured', $featuredBooleans);
        }

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

        if (isset($data['main_image']) && $data['main_image']) {
            $brandData['main_image'] = $this->handleMainImageUpload($data['main_image']);
        }

        if (isset($data['logo']) && $data['logo'] && !isset($data['main_image'])) {
            $brandData['main_image'] = $this->handleMainImageUpload($data['logo']);
        }

        if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
            $brandData['gallery_images'] = $this->handleGalleryImagesUpload($data['gallery_images']);
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
        $brand = Brand::findOrFail($id);
        
        if (!isset($data['name']) || empty($data['name'])) {
            $data['name'] = $brand->name;
        }
        
        $this->validateData($data, true, $id);
        
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

        if (isset($data['slug']) && $data['slug'] !== $brand->slug) {
            $updateData['slug'] = $data['slug'];
        }

        if (isset($data['main_image']) && $data['main_image']) {
            if ($brand->main_image) {
                $this->deleteMainImage($brand->main_image);
            }
            $updateData['main_image'] = $this->handleMainImageUpload($data['main_image']);
        }

        if (isset($data['logo']) && $data['logo'] && !isset($data['main_image'])) {
            if ($brand->main_image) {
                $this->deleteMainImage($brand->main_image);
            }
            $updateData['main_image'] = $this->handleMainImageUpload($data['logo']);
        }

        if (isset($data['remove_main_image']) && $data['remove_main_image']) {
            if ($brand->main_image) {
                $this->deleteMainImage($brand->main_image);
            }
            $updateData['main_image'] = null;
        }

        if (isset($data['gallery_images']) && is_array($data['gallery_images']) && !empty($data['gallery_images'])) {
            $newImages = $this->handleGalleryImagesUpload($data['gallery_images']);
            $existingImages = $brand->gallery_images ?? [];
            $updateData['gallery_images'] = array_merge($existingImages, $newImages);
        }

        if (isset($data['remove_gallery_images']) && is_array($data['remove_gallery_images']) && !empty($data['remove_gallery_images'])) {
            $this->deleteGalleryImages($data['remove_gallery_images']);
            $currentGallery = $updateData['gallery_images'] ?? ($brand->gallery_images ?? []);
            $updateData['gallery_images'] = array_values(array_diff($currentGallery, $data['remove_gallery_images']));
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
        
        if ($brand->main_image) {
            $this->deleteMainImage($brand->main_image);
        }

        if ($brand->gallery_images && is_array($brand->gallery_images)) {
            $this->deleteGalleryImages($brand->gallery_images);
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
            if ($brand->main_image) {
                $this->deleteMainImage($brand->main_image);
            }

            if ($brand->gallery_images && is_array($brand->gallery_images)) {
                $this->deleteGalleryImages($brand->gallery_images);
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
     * Handle main image upload.
     *
     * @param mixed $mainImage
     * @return string
     */
    protected function handleMainImageUpload($mainImage)
    {
        if (is_string($mainImage) && filter_var($mainImage, FILTER_VALIDATE_URL)) {
            return $mainImage;
        }

        if (is_file($mainImage)) {
            $path = $mainImage->store('brands', 'public');
            return $path;
        }

        return $mainImage;
    }

    /**
     * Delete main image file.
     *
     * @param string $imagePath
     * @return void
     */
    protected function deleteMainImage($imagePath)
    {
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            return; 
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Handle gallery images upload.
     *
     * @param array $images
     * @return array
     */
    protected function handleGalleryImagesUpload($images)
    {
        $uploadedImages = [];
        
        foreach ($images as $image) {
            if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
                $uploadedImages[] = $image;
            } elseif (is_file($image)) {
                $path = $image->store('brands/gallery', 'public');
                $uploadedImages[] = $path;
            }
        }
        
        return $uploadedImages;
    }

    /**
     * Delete gallery images.
     *
     * @param array $imagePaths
     * @return void
     */
    protected function deleteGalleryImages($imagePaths)
    {
        foreach ($imagePaths as $imagePath) {
            if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
                continue;
            }

            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
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
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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

