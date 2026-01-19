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
        return Order::where('customer_id', $customerId)->get();
    }


    /**
     * Get completed orders for the authenticated user
     */
    public function getCompletedOrders($customerId)
    {
        return Order::where('customer_id', $customerId)
            ->where('status', 'completed')
            ->get();
    }

    public function getPendingOrders($customerId)
    {
        return Order::where('customer_id', $customerId)
            ->where('status', 'pending')
            ->get();
    }
}
