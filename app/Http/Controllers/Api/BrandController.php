<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Brand\BrandRepository;
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
                'is_active' => $request->get('is_active', true), // Default to active only
                'is_featured' => $request->get('is_featured'),
                'search' => $request->get('search'),
            ];

            $brands = $this->brandRepository->getAllBrands($filters);

            return response()->json([
                'success' => true,
                'data' => $brands,
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
     * Get a single brand by ID.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $brand = $this->brandRepository->getBrandById($id);

            // Check if brand is active (optional - remove if you want to show inactive brands)
            if (!$brand->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Brand not found or inactive',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $brand,
                'message' => 'Brand retrieved successfully',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Brand not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve brand',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

