<?php

namespace App\Repositories\Admin\Tag;

use App\Models\Admin\Tag\Tag;
use Illuminate\Validation\ValidationException;

class TagRepository
{
    /**
     * Get all tags with ordering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllTags(array $filters = [])
    {
        $query = Tag::query();

        // Apply status filter
        if (isset($filters['is_active']) && !empty($filters['is_active'])) {
            $statusValues = is_array($filters['is_active']) ? $filters['is_active'] : [$filters['is_active']];
            $statusBooleans = array_map(function($val) {
                return $val === 'true' || $val === true || $val === '1' || $val === 1;
            }, $statusValues);
            $query->whereIn('is_active', $statusBooleans);
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
     * Get a tag by ID.
     *
     * @param int $id
     * @return Tag
     */
    public function getTagById($id)
    {
        return Tag::findOrFail($id);
    }

    /**
     * Create a new tag.
     *
     * @param array $data
     * @return Tag
     */
    public function store($data)
    {
        $this->validateData($data);
        return Tag::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }

    /**
     * Update a tag.
     *
     * @param int $id
     * @param array $data
     * @return Tag
     */
    public function update($id, $data)
    {
        $this->validateData($data, true, $id);
        $tag = Tag::findOrFail($id);
        
        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? null,
            'is_active' => $data['is_active'] ?? $tag->is_active,
            'sort_order' => $data['sort_order'] ?? $tag->sort_order,
        ];

        // Update slug only if explicitly provided
        if (isset($data['slug']) && $data['slug'] !== $tag->slug) {
            $updateData['slug'] = $data['slug'];
        }

        $tag->update($updateData);
        return $tag->fresh();
    }

    /**
     * Delete a tag.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $tag = Tag::findOrFail($id);
        return $tag->delete();
    }

    /**
     * Bulk delete tags.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete(array $ids)
    {
        $tags = Tag::whereIn('id', $ids)->get();

        foreach ($tags as $tag) {
            $tag->delete();
        }

        return true;
    }

    /**
     * Toggle tag active status.
     *
     * @param int $id
     * @return Tag
     */
    public function toggleStatus($id)
    {
        $tag = Tag::findOrFail($id);
        $tag->update(['is_active' => !$tag->is_active]);
        return $tag;
    }

    /**
     * Get tags for dropdown.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTagsForDropdown()
    {
        return Tag::active()
            ->ordered()
            ->get()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
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
            'total' => Tag::count(),
            'active' => Tag::active()->count(),
        ];
    }

    /**
     * Validate tag data.
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
            'slug' => 'nullable|string|max:255|unique:tags,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ];

        if ($isUpdate && $id) {
            $rules['slug'] = 'nullable|string|max:255|unique:tags,slug,' . $id;
        }

        return validator($data, $rules)->validate();
    }
}

