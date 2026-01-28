<?php

namespace App\Repositories\Api;

use App\Models\Order;

class OrderRepository
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get all orders for the authenticated user
     */
    public function createOrder(array $data)
    {
        return Order::create($data);
    }

    public function getAllOrders($customerId)
    {
        return Order::with(['orderItems.product', 'address'])
            ->where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->get();
    }


    /**
     * Get completed orders for the authenticated user
     */
    public function getCompletedOrders($customerId)
    {
        return Order::with(['orderItems.product', 'address'])
            ->where('customer_id', $customerId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPendingOrders($customerId)
    {
        return Order::with(['orderItems.product', 'address'])
            ->where('customer_id', $customerId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    public function getOrderById($orderId, $customerId)
    {
        return Order::with(['orderItems.product', 'address'])
            ->where('id', $orderId)
            ->where('customer_id', $customerId)
            ->first();
    }
}
