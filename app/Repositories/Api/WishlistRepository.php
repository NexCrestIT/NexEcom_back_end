<?php

namespace App\Repositories\Api;

use App\Models\Admin\Product\Product;
use App\Models\Wishlist;

class WishlistRepository
{
   public function getWishlistItems(int $customerId)
    {
        return Wishlist::with('product')
            ->where('customer_id', $customerId)
            ->latest()
            ->get();
    }

    public function addToWishlist(int $customerId, int $productId): Wishlist
    {
        $product = Product::findOrFail($productId);

        return Wishlist::firstOrCreate([
            'customer_id' => $customerId,
            'product_id' => $productId,
        ]);
    }

    public function removeFromWishlist(int $customerId, int $wishlistId): bool
    {
        $item = Wishlist::where('customer_id', $customerId)
            ->findOrFail($wishlistId);

        return $item->delete();
    }

    public function clearWishlist(int $customerId): int
    {
        return Wishlist::where('customer_id', $customerId)->delete();
    }

    public function getWishlistSummary(int $customerId): array
    {
        $count = Wishlist::where('customer_id', $customerId)->count();

        return [
            'total_items' => $count
        ];
    }
}
