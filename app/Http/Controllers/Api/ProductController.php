<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepository $productRepository
    ) {}

    /**
     * Get paginated list of products.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->get('per_page', $request->per_page);
        $perPage = min(max($perPage, 1), 100); // Limit between 1 and 100

        $filters = $request->only([
            'category_id',
            'brand_id',
            'collection_id',
            'gender_id',
            'scent_family_id',
            'is_featured',
            'is_new',
            'is_bestseller',
            'min_price',
            'max_price',
            'search',
            'sort_by',
            'sort_order',
        ]);

        $products = $this->productRepository->getProducts($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Get a single product by ID or slug.
     */
    public function show(string $id): JsonResponse
    {
        $product = $this->productRepository->getProductByIdOrSlug($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }

    /**
     * Get featured products.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $limit = min(max($limit, 1), 50); // Limit between 1 and 50

        $products = $this->productRepository->getFeaturedProducts($limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get new products.
     */
    public function newProducts(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $limit = min(max($limit, 1), 50); // Limit between 1 and 50

        $products = $this->productRepository->getNewProducts($limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get bestseller products.
     */
    public function bestsellers(Request $request): JsonResponse
    {
        $limit = (int) $request->get('limit', 10);
        $limit = min(max($limit, 1), 50); // Limit between 1 and 50

        $products = $this->productRepository->getBestsellerProducts($limit);

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }
}
