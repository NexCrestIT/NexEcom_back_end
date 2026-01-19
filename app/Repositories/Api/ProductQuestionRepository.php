<?php

namespace App\Repositories\Api;

use App\Models\ProductQuestion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductQuestionRepository
{
    /**
     * Get all questions for a product with pagination
     */
    public function getProductQuestions(int $productId, bool $publicOnly = true, int $perPage = 10): LengthAwarePaginator
    {
        $query = ProductQuestion::where('product_id', $productId)
            ->with(['customer:id,name,email', 'answerer:id,name']);

        if ($publicOnly) {
            $query->public();
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Get answered questions for a product
     */
    public function getAnsweredQuestions(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        return ProductQuestion::where('product_id', $productId)
            ->public()
            ->answered()
            ->with(['customer:id,name,email', 'answerer:id,name'])
            ->latest('answered_at')
            ->paginate($perPage);
    }

    /**
     * Get pending questions for a product
     */
    public function getPendingQuestions(int $productId, int $perPage = 10): LengthAwarePaginator
    {
        return ProductQuestion::where('product_id', $productId)
            ->pending()
            ->with(['customer:id,name,email'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single question by ID
     */
    public function getQuestion(int $questionId): ?ProductQuestion
    {
        return ProductQuestion::with(['customer:id,name,email', 'answerer:id,name'])
            ->find($questionId);
    }

    /**
     * Create a new question
     */
    public function createQuestion(array $data): ProductQuestion
    {
        return ProductQuestion::create($data);
    }

    /**
     * Update a question
     */
    public function updateQuestion(int $questionId, array $data): ProductQuestion
    {
        $question = ProductQuestion::findOrFail($questionId);
        $question->update($data);
        return $question->fresh(['customer:id,name,email', 'answerer:id,name']);
    }

    /**
     * Delete a question
     */
    public function deleteQuestion(int $questionId): bool
    {
        return ProductQuestion::destroy($questionId) > 0;
    }

    /**
     * Answer a question
     */
    public function answerQuestion(int $questionId, string $answer, int $answeredBy): ProductQuestion
    {
        $question = ProductQuestion::findOrFail($questionId);
        $question->update([
            'answer' => $answer,
            'answered_by' => $answeredBy,
            'answered_at' => now(),
            'status' => 'answered',
        ]);
        return $question->fresh(['customer:id,name,email', 'answerer:id,name']);
    }

    /**
     * Check if customer already asked about this product
     */
    public function hasCustomerAsked(int $productId, int $customerId, string $question): bool
    {
        return ProductQuestion::where('product_id', $productId)
            ->where('customer_id', $customerId)
            ->where('question', $question)
            ->exists();
    }

    /**
     * Get all questions by a customer
     */
    public function getCustomerQuestions(int $customerId, int $perPage = 10): LengthAwarePaginator
    {
        return ProductQuestion::where('customer_id', $customerId)
            ->with(['product:id,name,slug,main_image', 'answerer:id,name'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Increment helpful count for a question
     */
    public function incrementHelpful(int $questionId): bool
    {
        $question = ProductQuestion::find($questionId);
        if ($question) {
            $question->increment('helpful_count');
            return true;
        }
        return false;
    }

    /**
     * Get question statistics for a product
     */
    public function getQuestionStats(int $productId): array
    {
        $total = ProductQuestion::where('product_id', $productId)->public()->count();
        $answered = ProductQuestion::where('product_id', $productId)->public()->answered()->count();
        $pending = ProductQuestion::where('product_id', $productId)->public()->pending()->count();

        return [
            'total_questions' => $total,
            'answered_questions' => $answered,
            'pending_questions' => $pending,
            'answer_rate' => $total > 0 ? round(($answered / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Toggle question visibility
     */
    public function toggleVisibility(int $questionId): ProductQuestion
    {
        $question = ProductQuestion::findOrFail($questionId);
        $question->update(['is_public' => !$question->is_public]);
        return $question->fresh(['customer:id,name,email', 'answerer:id,name']);
    }
}
