<?php

namespace App\Repositories\Api;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository
{
    /**
     * Get all reviews for a product with pagination
     */
    public function getProductReviews(int $productId, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('product_id', $productId)
            ->with('customer:id,name,email')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get reviews by rating with pagination
     */
    public function getReviewsByRating(int $productId, int $rating, int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('product_id', $productId)
            ->where('rating', $rating)
            ->with('customer:id,name,email')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get review statistics for a product
     */
    public function getReviewStats(int $productId): array
    {
        return [
            'average_rating' => round(Review::where('product_id', $productId)->avg('rating') ?? 0, 2),
            'total_reviews' => Review::where('product_id', $productId)->count(),
            'rating_distribution' => $this->getRatingDistribution($productId),
        ];
    }

    /**
     * Get rating distribution for a product
     */
    public function getRatingDistribution(int $productId): array
    {
        $distribution = [];
        
        for ($rating = 5; $rating >= 1; $rating--) {
            $count = Review::where('product_id', $productId)
                ->where('rating', $rating)
                ->count();
            $distribution[$rating] = $count;
        }

        return $distribution;
    }

    /**
     * Check if customer already reviewed a product
     */
    public function hasCustomerReviewed(int $productId, int $customerId): bool
    {
        return Review::where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->exists();
    }

    /**
     * Get customer's review for a product
     */
    public function getCustomerReview(int $productId, int $customerId): ?Review
    {
        return Review::where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->first();
    }

    /**
     * Create a review
     */
    public function createReview(array $data): Review
    {
        return Review::create($data);
    }

    /**
     * Update a review
     */
    public function updateReview(int $reviewId, array $data): Review
    {
        $review = Review::findOrFail($reviewId);
        $review->update($data);
        return $review;
    }

    /**
     * Delete a review
     */
    public function deleteReview(int $reviewId): bool
    {
        return Review::destroy($reviewId) > 0;
    }

    /**
     * Get a single review by ID
     */
    public function getReview(int $reviewId): ?Review
    {
        return Review::with('customer:id,name,email')->find($reviewId);
    }

    /**
     * Get all reviews by a customer
     */
    public function getCustomerReviews(int $customerId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('customer_id', $customerId)
            ->with('product:id,name,slug,image')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get verified purchase reviews for a product
     */
    public function getVerifiedReviews(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('product_id', $productId)
            ->where('is_verified_purchase', true)
            ->with('customer:id,name,email')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Increment helpful count for a review
     */
    public function incrementHelpful(int $reviewId): bool
    {
        $review = Review::find($reviewId);
        if ($review) {
            $review->increment('helpful_count');
            return true;
        }
        return false;
    }

    /**
     * Get top rated reviews for a product
     */
    public function getTopReviews(int $productId, int $limit = 5): Collection
    {
        return Review::where('product_id', $productId)
            ->orderBy('rating', 'desc')
            ->orderBy('helpful_count', 'desc')
            ->with('customer:id,name,email')
            ->limit($limit)
            ->get();
    }
}
