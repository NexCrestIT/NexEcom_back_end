<?php

namespace App\Repositories\Api;

use App\Models\Carousel;
use Illuminate\Pagination\LengthAwarePaginator;

class CarouselRepository
{
    /**
     * Get all active carousels with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllActive(int $perPage = 10): LengthAwarePaginator
    {
        return Carousel::active()
            ->ordered()
            ->paginate($perPage);
    }

    /**
     * Get all carousels (including inactive) with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return Carousel::ordered()
            ->paginate($perPage);
    }

    /**
     * Get carousel by ID.
     *
     * @param int $id
     * @return Carousel
     */
    public function getById(int $id): Carousel
    {
        return Carousel::findOrFail($id);
    }

    /**
     * Create a new carousel.
     *
     * @param array $data
     * @return Carousel
     */
    public function create(array $data): Carousel
    {
        return Carousel::create($data);
    }

    /**
     * Update carousel.
     *
     * @param int $id
     * @param array $data
     * @return Carousel
     */
    public function update(int $id, array $data): Carousel
    {
        $carousel = $this->getById($id);
        $carousel->update($data);
        return $carousel;
    }

    /**
     * Delete carousel.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->getById($id)->delete();
    }

    /**
     * Toggle carousel active status.
     *
     * @param int $id
     * @return Carousel
     */
    public function toggleStatus(int $id): Carousel
    {
        $carousel = $this->getById($id);
        $carousel->update(['is_active' => !$carousel->is_active]);
        return $carousel;
    }

    /**
     * Get carousel statistics.
     *
     * @return array
     */
    public function getStats(): array
    {
        return [
            'total_carousels' => Carousel::count(),
            'active_carousels' => Carousel::active()->count(),
            'inactive_carousels' => Carousel::where('is_active', false)->count(),
        ];
    }
}
