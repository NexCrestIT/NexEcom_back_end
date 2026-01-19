<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\CarouselRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarouselController extends Controller
{
    protected CarouselRepository $carouselRepository;

    public function __construct(CarouselRepository $carouselRepository)
    {
        $this->carouselRepository = $carouselRepository;
    }

    /**
     * Get all active carousels.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $carousels = $this->carouselRepository->getAllActive();
            $stats = $this->carouselRepository->getStats();

            return response()->json([
                'success' => true,
                'data' => $carousels->items(),
                'pagination' => [
                    'current_page' => $carousels->currentPage(),
                    'last_page' => $carousels->lastPage(),
                    'per_page' => $carousels->perPage(),
                    'total' => $carousels->total(),
                ],
                'stats' => $stats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch carousels: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all carousels (including inactive) - Admin only.
     *
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        try {
            $carousels = $this->carouselRepository->getAll();
            $stats = $this->carouselRepository->getStats();

            return response()->json([
                'success' => true,
                'data' => $carousels->items(),
                'pagination' => [
                    'current_page' => $carousels->currentPage(),
                    'last_page' => $carousels->lastPage(),
                    'per_page' => $carousels->perPage(),
                    'total' => $carousels->total(),
                ],
                'stats' => $stats,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch carousels: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single carousel by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $carousel = $this->carouselRepository->getById($id);

            return response()->json([
                'success' => true,
                'data' => $carousel,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Carousel not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch carousel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new carousel - Admin only.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'button_name' => 'nullable|string|max:255',
                'button_url' => 'nullable|string|max:255',
                'image' => 'required|string',
                'is_active' => 'nullable|boolean',
            ]);

            $carousel = $this->carouselRepository->create($validated);

            return response()->json([
                'success' => true,
                'data' => $carousel,
                'message' => 'Carousel created successfully',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create carousel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update carousel - Admin only.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'subtitle' => 'nullable|string|max:255',
                'button_name' => 'nullable|string|max:255',
                'button_url' => 'nullable|string|max:255',
                'image' => 'sometimes|required|string',
                'is_active' => 'nullable|boolean',
            ]);

            $carousel = $this->carouselRepository->update($id, $validated);

            return response()->json([
                'success' => true,
                'data' => $carousel,
                'message' => 'Carousel updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Carousel not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update carousel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete carousel - Admin only.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->carouselRepository->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Carousel deleted successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Carousel not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete carousel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle carousel active status - Admin only.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $carousel = $this->carouselRepository->toggleStatus($id);

            return response()->json([
                'success' => true,
                'data' => $carousel,
                'message' => 'Carousel status updated successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Carousel not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle carousel status: ' . $e->getMessage(),
            ], 500);
        }
    }
}
