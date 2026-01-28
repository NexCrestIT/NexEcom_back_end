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
            config('razorpay.key_id'),
            config('razorpay.key_secret')
        );
    }

    /**
     * Create Razorpay payment link (does NOT create order yet)
     * Order will be created only after payment is verified
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createOrder(Request $request)
    {
        try {
            $customerId = Auth::id();
            
            $request->validate([
                'address_id' => 'required|exists:addresses,id,customer_id,' . $customerId,
                'notes' => 'nullable|string|max:500',
            ]);

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

            // Prepare cart items data for storage in payment link
            $cartItemsData = $cartItems->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price ?? $item->product->price,
                ];
            })->toArray();

            // Get customer details
            $customer = Auth::user();

            // Create Razorpay Payment Link
            // Store order data in notes so we can create order after payment
            $paymentLink = $this->razorpayApi->paymentLink->create([
                'amount' => $totalAmount * 100, // Amount in paise
                'currency' => config('razorpay.currency', 'INR'),
                'description' => 'Payment for NexCrest E-Commerce',
                'customer' => [
                    'name' => $customer->name ?? $customer->first_name . ' ' . $customer->last_name,
                    'email' => $customer->email,
                    'contact' => $customer->phone_number ?? $customer->phone ?? '',
                ],
                'notify' => [
                    'sms' => true,
                    'email' => true,
                ],
                'reminder_enable' => true,
                'notes' => [
                    'customer_id' => $customerId,
                    'address_id' => $request->address_id,
                    'cart_items' => json_encode($cartItemsData),
                    'order_notes' => $request->notes,
                    'total_amount' => $totalAmount,
                ],
                'callback_url' => env('APP_URL') . '/api/v1/razorpay/payment-callback',
                'callback_method' => 'get',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment link created. Please complete payment to create order.',
                'data' => [
                    'payment_link' => $paymentLink->short_url,
                    'payment_link_id' => $paymentLink->id,
                    'razorpay_key_id' => config('razorpay.key_id'),
                    'amount' => $totalAmount,
                    'currency' => config('razorpay.currency', 'INR'),
                    'expires_at' => $paymentLink->expire_by ?? null,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment link',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify Razorpay payment signature and create order
     * Order is created ONLY after payment verification succeeds
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
                return response()->json([
                    'success' => false,
                    'message' => 'Payment signature verification failed',
                    'error' => $e->getMessage()
                ], 400);
            }

            // Payment verified - Now create the order
            $customerId = Auth::id();
            
            // Get cart items
            $cartItems = Cart::with('product')->where('customer_id', $customerId)->get();
            
            if ($cartItems->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty'
                ], 400);
            }

            // Create order now that payment is verified
            $order = null;
            DB::transaction(function () use ($customerId, $request, $cartItems, &$order) {
                // Calculate total amount
                $totalAmount = $cartItems->sum(function ($item) {
                    return ($item->price ?? $item->product->price ?? 0) * $item->quantity;
                });

                // Create order in database
                $order = Order::create([
                    'customer_id' => $customerId,
                    'address_id' => $request->address_id ?? null,
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'total_amount' => $totalAmount,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'razorpay',
                    'razorpay_order_id' => $request->razorpay_order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'paid_at' => now(),
                ]);

                // Create order items
                foreach ($cartItems as $cartItem) {
                    $price = $cartItem->price ?? $cartItem->product->price;
                    $order->orderItems()->create([
                        'product_id' => $cartItem->product_id,
                        'quantity' => $cartItem->quantity,
                        'price' => $price,
                        'total' => $price * $cartItem->quantity,
                    ]);
                }

                // Clear cart after successful payment
                Cart::where('customer_id', $customerId)->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Payment verified and order created successfully',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'payment_status' => 'paid',
                    'status' => 'completed',
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification or order creation failed',
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

    /**
     * Handle payment callback from Razorpay payment link
     * Creates order ONLY after payment is successful
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function paymentCallback(Request $request)
    {
        try {
            // Log all request data for debugging
            \Log::info('Razorpay Payment Callback', $request->all());

            $paymentLinkId = $request->input('razorpay_payment_link_id');
            $paymentId = $request->input('razorpay_payment_id');
            $paymentLinkStatus = $request->input('razorpay_payment_link_status');

            if ($paymentLinkStatus === 'paid') {
                // Payment successful - Now create the order
                try {
                    // Fetch payment link details from Razorpay to get order data
                    $paymentLink = $this->razorpayApi->paymentLink->fetch($paymentLinkId);
                    $notes = $paymentLink->notes ?? [];

                    $customerId = $notes['customer_id'] ?? null;
                    $addressId = $notes['address_id'] ?? null;
                    $orderNotes = $notes['order_notes'] ?? null;
                    $cartItemsData = json_decode($notes['cart_items'] ?? '[]', true);
                    $totalAmount = $notes['total_amount'] ?? 0;

                    if (!$customerId || empty($cartItemsData)) {
                        throw new \Exception('Invalid payment link data');
                    }

                    // Create order in database now that payment is confirmed
                    DB::transaction(function () use ($customerId, $addressId, $orderNotes, $cartItemsData, $totalAmount, $paymentLinkId, $paymentId) {
                        // Create order
                        $order = Order::create([
                            'customer_id' => $customerId,
                            'address_id' => $addressId,
                            'order_number' => 'ORD-' . strtoupper(uniqid()),
                            'total_amount' => $totalAmount,
                            'status' => 'completed',
                            'payment_status' => 'paid',
                            'payment_method' => 'razorpay',
                            'razorpay_order_id' => $paymentLinkId,
                            'razorpay_payment_id' => $paymentId,
                            'notes' => $orderNotes,
                            'paid_at' => now(),
                        ]);

                        // Create order items from cart data
                        foreach ($cartItemsData as $item) {
                            $order->orderItems()->create([
                                'product_id' => $item['product_id'],
                                'quantity' => $item['quantity'],
                                'price' => $item['price'],
                                'total' => $item['price'] * $item['quantity'],
                            ]);
                        }

                        // Clear cart after successful payment
                        Cart::where('customer_id', $customerId)->delete();
                    });

                    \Log::info('Order created from payment callback', ['customer_id' => $customerId, 'payment_id' => $paymentId]);

                    // Redirect to success page
                    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/payment-success?payment_link_id=' . $paymentLinkId);
                } catch (\Exception $e) {
                    \Log::error('Failed to create order from callback', ['error' => $e->getMessage(), 'payment_link_id' => $paymentLinkId]);
                    return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/payment-failed?error=order_creation_failed');
                }
            } else {
                // Payment failed or cancelled
                \Log::warning('Payment not completed', ['status' => $paymentLinkStatus, 'payment_link_id' => $paymentLinkId]);

                return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/payment-failed?status=' . $paymentLinkStatus);
            }

        } catch (\Exception $e) {
            \Log::error('Payment callback error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect(env('FRONTEND_URL', 'http://localhost:3000') . '/payment-failed?error=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Handle Razorpay webhook for payment updates
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        try {
            // Verify webhook signature
            $webhookSecret = config('razorpay.webhook_secret');
            $webhookSignature = $request->header('X-Razorpay-Signature');
            $webhookBody = $request->getContent();

            if ($webhookSecret) {
                $expectedSignature = hash_hmac('sha256', $webhookBody, $webhookSecret);
                
                if ($webhookSignature !== $expectedSignature) {
                    \Log::error('Invalid webhook signature');
                    return response()->json(['status' => 'invalid signature'], 400);
                }
            }

            $event = $request->input('event');
            $payload = $request->input('payload');

            \Log::info('Razorpay Webhook Received', ['event' => $event]);

            // Handle payment link paid event
            if ($event === 'payment_link.paid') {
                $paymentLink = $payload['payment_link']['entity'] ?? null;
                $payment = $payload['payment']['entity'] ?? null;

                if ($paymentLink && $payment) {
                    $paymentLinkId = $paymentLink['id'];
                    $paymentId = $payment['id'];
                    $notes = $paymentLink['notes'] ?? [];

                    // Check if order already exists (to avoid duplicate creation)
                    $order = Order::where('razorpay_order_id', $paymentLinkId)->first();

                    if (!$order) {
                        // Order doesn't exist, create it now (payment confirmed)
                        try {
                            $customerId = $notes['customer_id'] ?? null;
                            $addressId = $notes['address_id'] ?? null;
                            $orderNotes = $notes['order_notes'] ?? null;
                            $cartItemsData = json_decode($notes['cart_items'] ?? '[]', true);
                            $totalAmount = $notes['total_amount'] ?? 0;

                            if (!$customerId || empty($cartItemsData)) {
                                throw new \Exception('Invalid payment link data in webhook');
                            }

                            DB::transaction(function () use ($customerId, $addressId, $orderNotes, $cartItemsData, $totalAmount, $paymentLinkId, $paymentId) {
                                // Create order
                                $order = Order::create([
                                    'customer_id' => $customerId,
                                    'address_id' => $addressId,
                                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                                    'total_amount' => $totalAmount,
                                    'status' => 'completed',
                                    'payment_status' => 'paid',
                                    'payment_method' => 'razorpay',
                                    'razorpay_order_id' => $paymentLinkId,
                                    'razorpay_payment_id' => $paymentId,
                                    'notes' => $orderNotes,
                                    'paid_at' => now(),
                                ]);

                                // Create order items from cart data
                                foreach ($cartItemsData as $item) {
                                    $order->orderItems()->create([
                                        'product_id' => $item['product_id'],
                                        'quantity' => $item['quantity'],
                                        'price' => $item['price'],
                                        'total' => $item['price'] * $item['quantity'],
                                    ]);
                                }

                                // Clear cart
                                Cart::where('customer_id', $customerId)->delete();
                            });

                            \Log::info('Order created via webhook', ['customer_id' => $customerId, 'payment_link_id' => $paymentLinkId]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to create order via webhook', ['error' => $e->getMessage(), 'payment_link_id' => $paymentLinkId]);
                        }
                    }
                }
            }

            // Handle payment link cancelled/expired
            if (in_array($event, ['payment_link.cancelled', 'payment_link.expired'])) {
                // Just log - no order to cancel since order is only created after payment
                \Log::info('Payment link ' . $event, ['payment_link_id' => $payload['payment_link']['entity']['id'] ?? null]);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            \Log::error('Webhook processing error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
