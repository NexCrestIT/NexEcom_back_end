<?php

namespace App\Repositories\Api;

use App\Models\Admin\Product\Product;
use App\Models\Cart;
use Illuminate\Validation\ValidationException;
 
class CartRepository
{
    /**
     * Get all cart items for a customer.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCartItems(int $customerId)
    {
        return Cart::with(['product' => function ($query) {
            $query->with(['category', 'brand']);
        }])
            ->forCustomer($customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get cart summary (total items, subtotal, etc.).
     */
    public function getCartSummary(int $customerId): array
    {
        $items = $this->getCartItems($customerId);

        $totalItems = $items->sum('quantity');
        $subtotal = $items->sum('subtotal');

        return [
            'total_items' => $totalItems,
            'total_quantity' => $totalItems,
            'subtotal' => round($subtotal, 2),
            'items_count' => $items->count(),
        ];
    }

    /**
     * Add product to cart.
     *
     * @throws ValidationException
     */
    public function addToCart(int $customerId, array $data): Cart
    {
        $product = Product::find($data['product_id']);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => ['The selected product does not exist.'],
            ]);
        }

        // Validate product is active
        if (! $product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => ['This product is not available.'],
            ]);
        }

        // Check stock availability
        if ($product->track_inventory && $product->stock_quantity < ($data['quantity'] ?? 1)) {
            throw ValidationException::withMessages([
                'quantity' => ['Insufficient stock available.'],
            ]);
        }

        // Check if product already exists in cart
        $cartItem = Cart::where('customer_id', $customerId)
            ->where('product_id', $data['product_id'])
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + ($data['quantity'] ?? 1);

            // Check stock again with new quantity
            if ($product->track_inventory && $product->stock_quantity < $newQuantity) {
                throw ValidationException::withMessages([
                    'quantity' => ['Insufficient stock available.'],
                ]);
            }

            $cartItem->update([
                'quantity' => $newQuantity,
                'price' => $product->price, // Update price in case it changed
            ]);

            return $cartItem->fresh(['product']);
        }

        // Create new cart item
        $cartItem = Cart::create([
            'customer_id' => $customerId,
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'] ?? 1,
            'price' => $product->price,
            'attributes' => $data['attributes'] ?? null,
        ]);

        return $cartItem->load(['product' => function ($query) {
            $query->with(['category', 'brand']);
        }]);
    }

    /**
     * Update cart item quantity.
     *
     * @throws ValidationException
     */
    public function updateCartItem(int $customerId, int $cartId, array $data): Cart
    {
        $cartItem = Cart::where('customer_id', $customerId)
            ->findOrFail($cartId);

        $product = $cartItem->product;

        // Validate quantity
        if (isset($data['quantity'])) {
            if ($data['quantity'] <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => ['Quantity must be greater than 0.'],
                ]);
            }

            // Check stock availability
            if ($product->track_inventory && $product->stock_quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => ['Insufficient stock available.'],
                ]);
            }

            $cartItem->quantity = $data['quantity'];
        }

        // Update attributes if provided
        if (isset($data['attributes'])) {
            $cartItem->attributes = $data['attributes'];
        }

        // Update price if product price changed
        $cartItem->price = $product->price;

        $cartItem->save();

        return $cartItem->fresh(['product' => function ($query) {
            $query->with(['category', 'brand']);
        }]);
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart(int $customerId, int $cartId): bool
    {
        $cartItem = Cart::where('customer_id', $customerId)
            ->findOrFail($cartId);

        return $cartItem->delete();
    }

    /**
     * Clear all items from cart.
     *
     * @return int Number of deleted items
     */
    public function clearCart(int $customerId): int
    {
        return Cart::where('customer_id', $customerId)->delete();
    }
}
