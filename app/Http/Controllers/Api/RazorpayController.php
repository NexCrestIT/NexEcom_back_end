<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;

class RazorpayController extends Controller
{
    protected $razorpayApi;

    public function __construct()
    {
        $this->razorpayApi = new Api(
            env('RAZORPAY_KEY_ID'),
            env('RAZORPAY_KEY_SECRET')
        );
    }

    /**
     * Create Razorpay order
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(Request $request)
    {
        try {
            $request->validate([
                'address_id' => 'required|exists:addresses,id',
                'notes' => 'nullable|string|max:500',
            ]);

            $customerId = Auth::id();

            // Get cart items
            $cartItems = Cart::with('product')->where('customer_id', $customerId)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Calculate total amount
            $totalAmount = $cartItems->sum(function ($item) {
                return ($item->price ?? $item->product->price ?? 0) * $item->quantity;
            });

            // Create order in database first (with pending status)
            $order = Order::create([
                'customer_id' => $customerId,
                'address_id' => $request->address_id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'razorpay',
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                $order->orderItems()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price ?? $cartItem->product->price,
                    'subtotal' => ($cartItem->price ?? $cartItem->product->price) * $cartItem->quantity,
                ]);
            }

            // Create Razorpay order
            $razorpayOrder = $this->razorpayApi->order->create([
                'receipt' => $order->order_number,
                'amount' => $totalAmount * 100, // Amount in paise (smallest currency unit)
                'currency' => 'INR',
                'notes' => [
                    'order_id' => $order->id,
                    'customer_id' => $customerId,
                ]
            ]);

            // Update order with Razorpay order ID
            $order->update([
                'razorpay_order_id' => $razorpayOrder->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'razorpay_order_id' => $razorpayOrder->id,
                    'razorpay_key_id' => env('RAZORPAY_KEY_ID'),
                    'amount' => $totalAmount,
                    'currency' => 'INR',
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Razorpay payment signature
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPayment(Request $request)
    {
        try {
            $request->validate([
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
            ]);

            // Find order by Razorpay order ID
            $order = Order::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ];

            try {
                $this->razorpayApi->utility->verifyPaymentSignature($attributes);
            } catch (\Exception $e) {
                // Signature verification failed
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment signature verification failed',
                    'error' => $e->getMessage()
                ], 400);
            }

            // Payment successful - Update order
            DB::transaction(function () use ($order, $request) {
                $order->update([
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'payment_status' => 'paid',
                    'status' => 'confirmed',
                    'paid_at' => now(),
                ]);

                // Clear cart after successful payment
                Cart::where('customer_id', $order->customer_id)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment failure
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentFailed(Request $request)
    {
        try {
            $request->validate([
                'razorpay_order_id' => 'required|string',
                'error_code' => 'nullable|string',
                'error_description' => 'nullable|string',
            ]);

            $order = Order::where('razorpay_order_id', $request->razorpay_order_id)->first();

            if ($order) {
                $order->update([
                    'payment_status' => 'failed',
                    'status' => 'cancelled',
                    'payment_error' => $request->error_description ?? 'Payment failed',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment failed',
                'data' => [
                    'order_id' => $order->id ?? null,
                    'error_description' => $request->error_description,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment failure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment status
     * 
     * @param string $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentStatus($orderId)
    {
        try {
            $order = Order::where('id', $orderId)
                ->where('customer_id', Auth::id())
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
