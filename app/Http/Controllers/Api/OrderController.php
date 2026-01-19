<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\Api\OrderRepository;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get all orders for authenticated customer
     */
    public function index()
{
    $customerId = Auth::id();
    return response()->json(
        $this->orderRepository->getAllOrders($customerId)
    );
}

public function store(Request $request)
{
    $request->validate([
        'notes' => 'nullable|string',
    ]);

    $customerId = Auth::id();

    $data = $request->all();
    $data['customer_id'] = $customerId;

    $data['order_number'] ??= 'ORD-' . strtoupper(uniqid());
    $data['status'] ??= 'pending';

    if (empty($data['total_amount'])) {
        $cartItems = Cart::where('customer_id', $customerId)->get();
        $data['total_amount'] = $cartItems->sum(fn ($item) =>
            ($item->price ?? $item->product->price ?? 0) * $item->quantity
        );
    }

    return response()->json(
        $this->orderRepository->createOrder($data),
        201
    );
}

public function pending()
{
    return response()->json(
        $this->orderRepository->getPendingOrders(Auth::id())
    );
}

public function completed()
{
    return response()->json(
        $this->orderRepository->getCompletedOrders(Auth::id())
    );
}

}
