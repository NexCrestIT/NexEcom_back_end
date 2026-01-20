<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Admin\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Api\ReviewRepository;

class ReviewController extends Controller
{
    protected ReviewRepository $reviewRepository;

    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Get all reviews for a product
     */
    public function index(int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $reviews = $this->reviewRepository->getProductReviews($productId);
        $stats = $this->reviewRepository->getReviewStats($productId);

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'stats' => $stats,
            'message' => 'Reviews retrieved successfully',
        ]);
    }

    /**
     * Get reviews for a product with rating filter
     */
    public function getByRating(int $productId, int $rating): JsonResponse
    {
        Product::findOrFail($productId);

        if ($rating < 1 || $rating > 5) {
            return response()->json([
                'success' => false,
                'message' => 'Rating must be between 1 and 5',
            ], 400);
        }

        $reviews = $this->reviewRepository->getReviewsByRating($productId, $rating);

        return response()->json([
            'success' => true,
            'data' => $reviews,
            'message' => 'Reviews filtered by rating',
        ]);
    }

    /**
     * Store a new review for a product
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        Product::findOrFail($productId);
        
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:10',
        ]);

        // Check if customer already reviewed this product
        if ($this->reviewRepository->hasCustomerReviewed($productId, auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'You have already reviewed this product',
            ], 400);
        }

        $review = $this->reviewRepository->createReview([
            'product_id' => $productId,
            'customer_id' => auth()->id(),
            'rating' => $validated['rating'],
            'title' => $validated['title'],
            'comment' => $validated['comment'],
            'is_verified_purchase' => $this->isVerifiedPurchase($productId),
        ]);

        return response()->json([
            'success' => true,
            'data' => $review->load('customer:id,name,email'),
            'message' => 'Review added successfully',
        ], 201);
    }

    /**
     * Update a review
     */
    public function update(Request $request, int $reviewId): JsonResponse
    {
        $review = $this->reviewRepository->getReview($reviewId);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        // Check if user owns this review
        if ($review->customer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'title' => 'nullable|string|max:255',
            'comment' => 'sometimes|string|min:10',
        ]);

        $updatedReview = $this->reviewRepository->updateReview($reviewId, $validated);

        return response()->json([
            'success' => true,
            'data' => $updatedReview->load('customer:id,name,email'),
            'message' => 'Review updated successfully',
        ]);
    }

    /**
     * Delete a review
     */
    public function destroy(int $reviewId): JsonResponse
    {
        $review = $this->reviewRepository->getReview($reviewId);

        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found',
            ], 404);
        }

        // Check if user owns this review
        if ($review->customer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->reviewRepository->deleteReview($reviewId);

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Check if customer has a verified purchase of the product
     */
    private function isVerifiedPurchase(int $productId): bool
    {
        return \App\Models\Order::where('customer_id', auth()->id())
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->where('status', 'completed')
            ->exists();
    }
}
