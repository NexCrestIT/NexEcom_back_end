<?php

namespace App\Repositories\Admin\Collection;

use App\Models\Admin\Collection\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CollectionRepository
{
    /**
     * Get all collections with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCollections(array $filters = [])
    {
        $query = Collection::query();

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
     * Get a collection by ID.
     *
     * @param int $id
     * @return Collection
     */
    public function getCollectionById($id)
    {
        return Collection::findOrFail($id);
    }

    /**
     * Create a new collection.
     *
     * @param array $data
     * @return Collection
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $collectionData = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            $collectionData['image'] = $this->handleImageUpload($data['image']);
        }

        // Handle banner upload
        if (isset($data['banner']) && $data['banner']) {
            $collectionData['banner'] = $this->handleImageUpload($data['banner'], 'banners');
        }

        return Collection::create($collectionData);
    }

    /**
     * Update a collection.
     *
     * @param int $id
     * @param array $data
     * @return Collection
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $collection = Collection::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? $collection->is_active,
            'is_featured' => $data['is_featured'] ?? $collection->is_featured,
            'sort_order' => $data['sort_order'] ?? $collection->sort_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $collection->slug) {
            $updateData['slug'] = $data['slug'];
        }

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            // Delete old image if exists
            if ($collection->image) {
                $this->deleteImage($collection->image);
            }
            $updateData['image'] = $this->handleImageUpload($data['image']);
        }

        // Handle banner upload
        if (isset($data['banner']) && $data['banner']) {
            // Delete old banner if exists
            if ($collection->banner) {
                $this->deleteImage($collection->banner);
            }
            $updateData['banner'] = $this->handleImageUpload($data['banner'], 'banners');
        }

        $collection->update($updateData);
        return $collection->fresh();
    }

    /**
     * Delete a collection.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $collection = Collection::findOrFail($id);
        
        // Delete images if exist
        if ($collection->image) {
            $this->deleteImage($collection->image);
        }
        if ($collection->banner) {
            $this->deleteImage($collection->banner);
        }
        
        return $collection->delete();
    }

    /**
     * Bulk delete collections.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $collections = Collection::whereIn('id', $ids)->get();

        foreach ($collections as $collection) {
            // Delete images if exist
            if ($collection->image) {
                $this->deleteImage($collection->image);
            }
            if ($collection->banner) {
                $this->deleteImage($collection->banner);
            }
            $collection->delete();
        }

        return true;
    }

    /**
     * Toggle collection active status.
     *
     * @param int $id
     * @return Collection
     */
    public function toggleStatus($id)
    {
        $collection = Collection::findOrFail($id);
        $collection->update(['is_active' => !$collection->is_active]);
        return $collection;
    }

    /**
     * Toggle collection featured status.
     *
     * @param int $id
     * @return Collection
     */
    public function toggleFeatured($id)
    {
        $collection = Collection::findOrFail($id);
        $collection->update(['is_featured' => !$collection->is_featured]);
        return $collection;
    }

    /**
     * Update sort order for multiple collections.
     *
     * @param array $sortData Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder(array $sortData)
    {
        foreach ($sortData as $id => $sortOrder) {
            Collection::where('id', $id)->update(['sort_order' => $sortOrder]);
        }
        return true;
    }

    /**
     * Get collections for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCollectionsForDropdown()
    {
        return Collection::active()
            ->ordered()
            ->get()
            ->map(function ($collection) {
                return [
                    'id' => $collection->id,
                    'name' => $collection->name,
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
            'total' => Collection::count(),
            'active' => Collection::active()->count(),
            'featured' => Collection::featured()->count(),
        ];
    }

    /**
     * Handle image upload.
     *
     * @param mixed $image
     * @param string $folder
     * @return string
     */
    protected function handleImageUpload($image, $folder = 'collections')
    {
        if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
            // If it's a URL, return as is
            return $image;
        }

        if (is_file($image)) {
            $path = $image->store($folder, 'public');
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
            return; // Don't delete external URLs
        }

        if (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Validate collection data.
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
            'slug' => 'nullable|string|max:255|unique:collections,slug',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string|max:255',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:collections,slug,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}

