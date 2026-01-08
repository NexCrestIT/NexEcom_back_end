<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\CartRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    public function __construct(
        private CartRepository $cartRepository
    ) {}

    /**
     * Get customer's cart items.
     */
    public function index(Request $request): JsonResponse
    {
        $customer = $request->user();
        $items = $this->cartRepository->getCartItems($customer->id);
        $summary = $this->cartRepository->getCartSummary($customer->id);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'summary' => $summary,
            ],
        ]);
    }

    /**
     * Add product to cart.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
            ],
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'attributes' => [
                'nullable',
                'array',
            ],
        ], [
            'product_id.required' => 'Product ID is required.',
            'product_id.exists' => 'The selected product is not available.',
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'attributes.array' => 'Attributes must be an array.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = $request->user();

        try {
            $cartItem = $this->cartRepository->addToCart(
                $customer->id,
                $validator->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully.',
                'data' => $cartItem,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found.',
                'errors' => [
                    'product_id' => ['The selected product does not exist.'],
                ],
            ], 404);
        }
    }

    /**
     * Update cart item.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'attributes' => [
                'nullable',
                'array',
            ],
        ], [
            'quantity.integer' => 'Quantity must be an integer.',
            'quantity.min' => 'Quantity must be at least 1.',
            'attributes.array' => 'Attributes must be an array.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer = $request->user();

        try {
            $cartItem = $this->cartRepository->updateCartItem(
                $customer->id,
                $id,
                $validator->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Cart item updated successfully.',
                'data' => $cartItem,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $customer = $request->user();

        $this->cartRepository->removeFromCart($customer->id, $id);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart successfully.',
        ]);
    }

    /**
     * Clear all items from cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $customer = $request->user();

        $deletedCount = $this->cartRepository->clearCart($customer->id);

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'data' => [
                'deleted_items' => $deletedCount,
            ],
        ]);
    }

    /**
     * Get cart summary.
     */
    public function summary(Request $request): JsonResponse
    {
        $customer = $request->user();
        $summary = $this->cartRepository->getCartSummary($customer->id);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
