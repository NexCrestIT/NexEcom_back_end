<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\WishlistRepository;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
   public function __construct(
        private WishlistRepository $wishlistRepository
    ) {}

    public function index(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'success' => true,
            'data' => $this->wishlistRepository->getWishlistItems($customer->id),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $customer = $request->user();

        $wishlist = $this->wishlistRepository->addToWishlist(
            $customer->id,
            $request->product_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Product added to wishlist',
            'data' => $wishlist,
        ], 201);
    }

    public function destroy(Request $request, int $id)
    {
        $customer = $request->user();

        $this->wishlistRepository->removeFromWishlist($customer->id, $id);

        return response()->json([
            'success' => true,
            'message' => 'Product removed from wishlist',
        ]);
    }

    public function clear(Request $request)
    {
        $customer = $request->user();

        $deleted = $this->wishlistRepository->clearWishlist($customer->id);

        return response()->json([
            'success' => true,
            'message' => 'Wishlist cleared',
            'data' => ['deleted_items' => $deleted],
        ]);
    }

    public function summary(Request $request)
    {
        $customer = $request->user();

        return response()->json([
            'success' => true,
            'data' => $this->wishlistRepository->getWishlistSummary($customer->id),
        ]);
    }
}
