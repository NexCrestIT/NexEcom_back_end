<?php

namespace App\Http\Controllers\Api;

use App\Models\Admin\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Repositories\Api\ProductQuestionRepository;

class ProductQuestionController extends Controller
{
    protected ProductQuestionRepository $questionRepository;

    public function __construct(ProductQuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;
    }

    /**
     * Get all questions for a product
     */
    public function index(int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $questions = $this->questionRepository->getProductQuestions($productId);
        $stats = $this->questionRepository->getQuestionStats($productId);

        return response()->json([
            'success' => true,
            'data' => $questions,
            'stats' => $stats,
            'message' => 'Questions retrieved successfully',
        ]);
    }

    /**
     * Get answered questions for a product
     */
    public function answered(int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $questions = $this->questionRepository->getAnsweredQuestions($productId);

        return response()->json([
            'success' => true,
            'data' => $questions,
            'message' => 'Answered questions retrieved successfully',
        ]);
    }

    /**
     * Get a single question
     */
    public function show(int $questionId): JsonResponse
    {
        $question = $this->questionRepository->getQuestion($questionId);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $question,
            'message' => 'Question retrieved successfully',
        ]);
    }

    /**
     * Ask a new question about a product
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        Product::findOrFail($productId);
        
        $validated = $request->validate([
            'question' => 'required|string|min:10|max:500',
        ]);

        // Check for duplicate question from same customer
        if ($this->questionRepository->hasCustomerAsked($productId, auth()->id(), $validated['question'])) {
            return response()->json([
                'success' => false,
                'message' => 'You have already asked this question',
            ], 400);
        }

        $question = $this->questionRepository->createQuestion([
            'product_id' => $productId,
            'customer_id' => auth()->id(),
            'question' => $validated['question'],
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'data' => $question->load('customer:id,name,email'),
            'message' => 'Question posted successfully',
        ], 201);
    }

    /**
     * Update a question
     */
    public function update(Request $request, int $questionId): JsonResponse
    {
        $question = $this->questionRepository->getQuestion($questionId);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        // Check if user owns this question
        if ($question->customer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Cannot update if already answered
        if ($question->status === 'answered') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update an answered question',
            ], 400);
        }

        $validated = $request->validate([
            'question' => 'sometimes|string|min:10|max:500',
        ]);

        $updatedQuestion = $this->questionRepository->updateQuestion($questionId, $validated);

        return response()->json([
            'success' => true,
            'data' => $updatedQuestion,
            'message' => 'Question updated successfully',
        ]);
    }

    /**
     * Delete a question
     */
    public function destroy(int $questionId): JsonResponse
    {
        $question = $this->questionRepository->getQuestion($questionId);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        // Check if user owns this question
        if ($question->customer_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->questionRepository->deleteQuestion($questionId);

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully',
        ]);
    }

    /**
     * Mark question as helpful
     */
    public function markHelpful(int $questionId): JsonResponse
    {
        $question = $this->questionRepository->getQuestion($questionId);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        $this->questionRepository->incrementHelpful($questionId);

        return response()->json([
            'success' => true,
            'message' => 'Question marked as helpful',
        ]);
    }

    /**
     * Answer a question (Admin/Staff only)
     */
    public function answer(Request $request, int $questionId): JsonResponse
    {
        $question = $this->questionRepository->getQuestion($questionId);

        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
            ], 404);
        }

        $validated = $request->validate([
            'answer' => 'required|string|min:10|max:2000',
        ]);

        $answeredQuestion = $this->questionRepository->answerQuestion(
            $questionId,
            $validated['answer'],
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'data' => $answeredQuestion,
            'message' => 'Question answered successfully',
        ]);
    }
}
