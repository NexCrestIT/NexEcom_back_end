<?php

namespace App\Repositories\Admin\Category;

use App\Models\Admin\Category\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryRepository
{
    /**
     * Get all categories with eager loaded relations.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCategories(array $filters = [])
    {
        $query = Category::with(['parent', 'children']);

        // Apply status filter (support array for multi-select)
        if (isset($filters['status']) && !empty($filters['status'])) {
            $statusValues = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
            $statusBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $statusValues);
            $query->whereIn('is_active', $statusBooleans);
        }

        // Apply featured filter (support array for multi-select)
        if (isset($filters['featured']) && !empty($filters['featured'])) {
            $featuredValues = is_array($filters['featured']) ? $filters['featured'] : [$filters['featured']];
            $featuredBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $featuredValues);
            $query->whereIn('is_featured', $featuredBooleans);
        }

        // Apply parent filter (support array for multi-select)
        if (isset($filters['parent_id']) && !empty($filters['parent_id'])) {
            $parentIds = is_array($filters['parent_id']) ? $filters['parent_id'] : [$filters['parent_id']];
            $query->whereIn('parent_id', $parentIds);
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
     * Get paginated categories.
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedCategories($perPage = 15)
    {
        return Category::with(['parent', 'children'])
            ->ordered()
            ->paginate($perPage);
    }

    /**
     * Get only root categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRootCategories()
    {
        return Category::root()
            ->with('descendants')
            ->ordered()
            ->get();
    }

    /**
     * Get active categories only.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveCategories()
    {
        return Category::active()
            ->with(['parent', 'activeChildren'])
            ->ordered()
            ->get();
    }

    /**
     * Get featured categories.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFeaturedCategories()
    {
        return Category::active()
            ->featured()
            ->with('activeChildren')
            ->ordered()
            ->get();
    }

    /**
     * Get categories as tree structure.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getCategoryTree()
    {
        $categories = Category::with('descendants')
            ->root()
            ->ordered()
            ->get();

        return $this->buildTree($categories);
    }

    /**
     * Build a hierarchical tree from categories.
     *
     * @param \Illuminate\Database\Eloquent\Collection $categories
     * @return \Illuminate\Support\Collection
     */
    protected function buildTree($categories)
    {
        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'image' => $category->image,
                'image_url' => $category->image_url,
                'is_active' => $category->is_active,
                'is_featured' => $category->is_featured,
                'sort_order' => $category->sort_order,
                'depth' => $category->depth,
                'full_path' => $category->full_path,
                'children_count' => $category->children->count(),
                'children' => $category->children->isNotEmpty() 
                    ? $this->buildTree($category->children) 
                    : [],
            ];
        });
    }

    /**
     * Get flattened categories for dropdowns.
     *
     * @param int|null $excludeId Category ID to exclude (and its descendants)
     * @return \Illuminate\Support\Collection
     */
    public function getCategoriesForDropdown($excludeId = null)
    {
        $categories = Category::ordered()->get();
        $result = collect();

        $rootCategories = $categories->whereNull('parent_id');

        foreach ($rootCategories as $category) {
            $this->flattenCategory($category, $categories, $result, 0, $excludeId);
        }

        return $result;
    }

    /**
     * Recursively flatten categories with proper indentation.
     */
    protected function flattenCategory($category, $allCategories, &$result, $depth, $excludeId = null)
    {
        if ($excludeId && ($category->id == $excludeId || in_array($category->id, Category::find($excludeId)?->getDescendantIds() ?? []))) {
            return;
        }

        $prefix = str_repeat('— ', $depth);
        $result->push([
            'id' => $category->id,
            'name' => $prefix . $category->name,
            'original_name' => $category->name,
            'depth' => $depth,
            'is_active' => $category->is_active,
        ]);

        $children = $allCategories->where('parent_id', $category->id)->sortBy('sort_order');
        foreach ($children as $child) {
            $this->flattenCategory($child, $allCategories, $result, $depth + 1, $excludeId);
        }
    }

    /**
     * Get category by ID.
     *
     * @param int $id
     * @return Category|null
     */
    public function getCategoryById($id)
    {
        return Category::with(['parent', 'children', 'descendants'])
            ->findOrFail($id);
    }

    /**
     * Get category by slug.
     *
     * @param string $slug
     * @return Category|null
     */
    public function getCategoryBySlug($slug)
    {
        return Category::with(['parent', 'activeChildren'])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * Store a new category.
     *
     * @param array $data
     * @return Category
     */
    public function store($data)
    {
        $this->validateData($data);

        $categoryData = [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_featured' => $data['is_featured'] ?? false,
            'sort_order' => $data['sort_order'] ?? 0,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            $categoryData['image'] = $this->handleImageUpload($data['image']);
        }

        return Category::create($categoryData);
    }

    /**
     * Update a category.
     *
     * @param int $id
     * @param array $data
     * @return Category
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);

        $category = Category::findOrFail($id);

        // Prevent setting parent to self or any of its descendants
        if (isset($data['parent_id']) && $data['parent_id']) {
            $descendantIds = $category->getDescendantIds();
            if ($data['parent_id'] == $id || in_array($data['parent_id'], $descendantIds)) {
                throw new \InvalidArgumentException('Cannot set parent to self or a descendant category.');
            }
        }

        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
            'is_active' => $data['is_active'] ?? $category->is_active,
            'is_featured' => $data['is_featured'] ?? $category->is_featured,
            'sort_order' => $data['sort_order'] ?? $category->sort_order,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'meta_keywords' => $data['meta_keywords'] ?? null,
        ];

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $category->slug) {
            $updateData['slug'] = $data['slug'];
        }

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            // Delete old image if exists
            if ($category->image) {
                $this->deleteImage($category->image);
            }
            $updateData['image'] = $this->handleImageUpload($data['image']);
        }

        // Handle image removal
        if (isset($data['remove_image']) && $data['remove_image'] && $category->image) {
            $this->deleteImage($category->image);
            $updateData['image'] = null;
        }

        $category->update($updateData);

        return $category->fresh(['parent', 'children']);
    }

    /**
     * Delete a category.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $category = Category::findOrFail($id);

        // Delete image if exists
        if ($category->image) {
            $this->deleteImage($category->image);
        }

        return $category->delete();
    }

    /**
     * Bulk delete categories.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $categories = Category::whereIn('id', $ids)->get();

        foreach ($categories as $category) {
            // Delete image if exists
            if ($category->image) {
                $this->deleteImage($category->image);
            }
            // Delete the category (soft delete will cascade to children)
            $category->delete();
        }

        return true;
    }

    /**
     * Force delete a category and all its descendants.
     *
     * @param int $id
     * @return bool
     */
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        // Delete all descendant images
        $this->deleteDescendantImages($category);

        // Delete image if exists
        if ($category->image) {
            $this->deleteImage($category->image);
        }

        return $category->forceDelete();
    }

    /**
     * Restore a soft-deleted category.
     *
     * @param int $id
     * @return Category
     */
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        return $category;
    }

    /**
     * Toggle category status.
     *
     * @param int $id
     * @return Category
     */
    public function toggleStatus($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        return $category;
    }

    /**
     * Toggle category featured status.
     *
     * @param int $id
     * @return Category
     */
    public function toggleFeatured($id)
    {
        $category = Category::findOrFail($id);
        $category->update(['is_featured' => !$category->is_featured]);

        return $category;
    }

    /**
     * Update sort order for multiple categories.
     *
     * @param array $sortData Array of [id => sort_order]
     * @return bool
     */
    public function updateSortOrder(array $sortData)
    {
        foreach ($sortData as $id => $sortOrder) {
            Category::where('id', $id)->update(['sort_order' => $sortOrder]);
        }

        return true;
    }

    /**
     * Move category to a new parent.
     *
     * @param int $id
     * @param int|null $newParentId
     * @return Category
     */
    public function moveCategory($id, $newParentId = null)
    {
        $category = Category::findOrFail($id);

        // Prevent moving to self or descendant
        if ($newParentId) {
            $descendantIds = $category->getDescendantIds();
            if ($newParentId == $id || in_array($newParentId, $descendantIds)) {
                throw new \InvalidArgumentException('Cannot move category to self or a descendant.');
            }
        }

        $category->update(['parent_id' => $newParentId]);

        return $category->fresh(['parent', 'children']);
    }

    /**
     * Handle image upload.
     *
     * @param \Illuminate\Http\UploadedFile $image
     * @return string
     */
    protected function handleImageUpload($image)
    {
        return $image->store('categories', 'public');
    }

    /**
     * Delete an image from storage.
     *
     * @param string $path
     * @return bool
     */
    protected function deleteImage($path)
    {
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }

        return false;
    }

    /**
     * Delete images for all descendants.
     *
     * @param Category $category
     */
    protected function deleteDescendantImages(Category $category)
    {
        foreach ($category->children as $child) {
            if ($child->image) {
                $this->deleteImage($child->image);
            }
            $this->deleteDescendantImages($child);
        }
    }

    /**
     * Validate category data.
     *
     * @param array $data
     * @param bool $isUpdate
     * @param int|null $id
     * @return array
     */
    public function validateData($data, $isUpdate = false, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'parent_id' => 'nullable|integer|exists:categories,id',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:categories,slug,' . $id;
        } else {
            $rules['slug'] = 'nullable|string|max:255|unique:categories,slug';
        }

        return validator($data, $rules)->validate();
    }

    /**
     * Search categories by name.
     *
     * @param string $term
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchCategories($term)
    {
        return Category::where('name', 'like', '%' . $term . '%')
            ->orWhere('description', 'like', '%' . $term . '%')
            ->with(['parent'])
            ->ordered()
            ->get();
    }

    /**
     * Get category statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total' => Category::count(),
            'active' => Category::active()->count(),
            'inactive' => Category::where('is_active', false)->count(),
            'featured' => Category::featured()->count(),
            'root' => Category::root()->count(),
            'with_children' => Category::has('children')->count(),
        ];
    }
}

