<?php

namespace App\Repositories\Admin\Carousel;

use App\Models\Carousel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CarouselRepository
{
    /**
     * Get all carousels with filtering.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllCarousels(array $filters = [])
    {
        $query = Carousel::query();

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
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subtitle', 'like', "%{$search}%")
                  ->orWhere('button_name', 'like', "%{$search}%");
            });
        }

        return $query->ordered()->get();
    }

    /**
     * Get a carousel by ID.
     *
     * @param int $id
     * @return Carousel
     */
    public function getCarouselById($id)
    {
        return Carousel::findOrFail($id);
    }

    /**
     * Create a new carousel.
     *
     * @param array $data
     * @return Carousel
     */
    public function store($data)
    {
        $this->validateData($data);
        
        $carouselData = [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? null,
            'button_name' => $data['button_name'] ?? null,
            'button_url' => $data['button_url'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            $carouselData['image'] = $this->handleImageUpload($data['image']);
        }

        return Carousel::create($carouselData);
    }

    /**
     * Update a carousel.
     *
     * @param int $id
     * @param array $data
     * @return Carousel
     */
    public function update($id, $data)
    {
        $carousel = $this->getCarouselById($id);
        
        // Set default values before validation
        if (!isset($data['title']) || empty($data['title'])) {
            $data['title'] = $carousel->title;
        }
        
        $this->validateData($data, true);
        
        $carouselData = [
            'title' => $data['title'],
            'subtitle' => $data['subtitle'] ?? $carousel->subtitle,
            'button_name' => $data['button_name'] ?? $carousel->button_name,
            'button_url' => $data['button_url'] ?? $carousel->button_url,
            'is_active' => isset($data['is_active']) ? $data['is_active'] : $carousel->is_active,
        ];

        // Handle image upload
        if (isset($data['image']) && $data['image']) {
            // Delete old image if exists
            if ($carousel->image) {
                $this->deleteImage($carousel->image);
            }
            $carouselData['image'] = $this->handleImageUpload($data['image']);
        }

        $carousel->update($carouselData);
        return $carousel;
    }

    /**
     * Delete a carousel.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        $carousel = $this->getCarouselById($id);
        
        // Delete image if exists
        if ($carousel->image) {
            $this->deleteImage($carousel->image);
        }
        
        return $carousel->delete();
    }

    /**
     * Toggle carousel active status.
     *
     * @param int $id
     * @return Carousel
     */
    public function toggleStatus($id)
    {
        $carousel = $this->getCarouselById($id);
        $carousel->update(['is_active' => !$carousel->is_active]);
        return $carousel;
    }

    /**
     * Bulk delete carousels.
     *
     * @param array $ids
     * @return bool
     */
    public function bulkDelete($ids)
    {
        $carousels = Carousel::whereIn('id', $ids)->get();
        
        foreach ($carousels as $carousel) {
            // Delete image if exists
            if ($carousel->image) {
                $this->deleteImage($carousel->image);
            }
            $carousel->delete();
        }
        
        return true;
    }

    /**
     * Get carousel statistics.
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_carousels' => Carousel::count(),
            'active_carousels' => Carousel::where('is_active', true)->count(),
            'inactive_carousels' => Carousel::where('is_active', false)->count(),
        ];
    }

    /**
     * Validate carousel data.
     *
     * @param array $data
     * @param bool $isUpdate
     * @throws ValidationException
     */
    private function validateData($data, $isUpdate = false)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'button_name' => 'nullable|string|max:255',
            'button_url' => 'nullable|string|max:255',
            'image' => $isUpdate ? 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048' : 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'nullable|boolean',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
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
            $path = $image->store('carousels', 'public');
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
}
