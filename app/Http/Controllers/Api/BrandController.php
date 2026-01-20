<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\BrandRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    protected $brandRepository;

    public function __construct(BrandRepository $brandRepository)
    {
        $this->brandRepository = $brandRepository;
    }

    /**
     * Get all active brands.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'is_featured' => $request->get('is_featured'),
                'search' => $request->get('search'),
            ];

            $brands = $this->brandRepository->getBrands($filters);

            return response()->json([
                'success' => true,
                'data' => $brands->values(),
                'message' => 'Brands retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brands',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single brand by ID or slug.
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $brand = $this->brandRepository->getBrandByIdOrSlug($id);

            if (!$brand) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $brand,
                'message' => 'Brand retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brand',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get featured brands.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $limit = (int) $request->get('limit', 10);
            $limit = min(max($limit, 1), 50); 

            $brands = $this->brandRepository->getFeaturedBrands($limit);

            return response()->json([
                'success' => true,
                'data' => $brands,
                'message' => 'Featured brands retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve featured brands',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
