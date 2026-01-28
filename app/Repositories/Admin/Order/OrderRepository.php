<?php

namespace App\Repositories\Admin\Order;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    /**
     * Get all orders with filters and pagination
     *
     * @param int $perPage
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedOrders($perPage = 15, array $filters = [])
    {
        $query = Order::with(['customer', 'items', 'address'])
            ->orderBy('created_at', 'desc');

        // Apply status filter
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Apply payment status filter
        if (isset($filters['payment_status']) && !empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        // Apply date range filter
        if (isset($filters['from_date']) && !empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && !empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        // Apply search filter
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Apply amount range filter
        if (isset($filters['min_amount']) && !empty($filters['min_amount'])) {
            $query->where('total_amount', '>=', (float)$filters['min_amount']);
        }

        if (isset($filters['max_amount']) && !empty($filters['max_amount'])) {
            $query->where('total_amount', '<=', (float)$filters['max_amount']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get order by ID with all relationships
     *
     * @param int $id
     * @return Order
     */
    public function getOrderById($id)
    {
        return Order::with(['customer', 'items.product', 'address', 'user'])
            ->findOrFail($id);
    }

    /**
     * Update order status
     *
     * @param int $id
     * @param string $status
     * @param string|null $notes
     * @return bool
     */
    public function updateOrderStatus($id, $status, $notes = null)
    {
        $order = $this->getOrderById($id);

        $updateData = ['status' => $status];
        if ($notes !== null) {
            $updateData['notes'] = $notes;
        }

        return $order->update($updateData);
    }

    /**
     * Update payment status
     *
     * @param int $id
     * @param string $paymentStatus
     * @return bool
     */
    public function updatePaymentStatus($id, $paymentStatus)
    {
        $order = $this->getOrderById($id);
        return $order->update(['payment_status' => $paymentStatus]);
    }

    /**
     * Get order statistics
     *
     * @return array
     */
    public function getStatistics()
    {
        return [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'completed')->sum('total_amount'),
            'pending_revenue' => Order::where('payment_status', 'pending')->sum('total_amount'),
        ];
    }

    /**
     * Get revenue statistics by date range
     *
     * @param string $fromDate
     * @param string $toDate
     * @return array
     */
    public function getRevenueByDateRange($fromDate, $toDate)
    {
        return Order::whereBetween('created_at', [$fromDate, $toDate])
            ->where('payment_status', 'completed')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as order_count, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get order status distribution
     *
     * @return array
     */
    public function getStatusDistribution()
    {
        return Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    /**
     * Get payment method distribution
     *
     * @return array
     */
    public function getPaymentMethodDistribution()
    {
        return Order::selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->where('payment_status', 'completed')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();
    }

    /**
     * Get top customers by order value
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopCustomers($limit = 10)
    {
        return Order::selectRaw('customer_id, COUNT(*) as order_count, SUM(total_amount) as total_spent')
            ->where('payment_status', 'completed')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->with('customer')
            ->get();
    }

    /**
     * Update order notes
     *
     * @param int $id
     * @param string $notes
     * @return bool
     */
    public function updateNotes($id, $notes)
    {
        return Order::findOrFail($id)->update(['notes' => $notes]);
    }

    /**
     * Delete order
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return Order::findOrFail($id)->delete();
    }

    /**
     * Get recent orders
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentOrders($limit = 10)
    {
        return Order::with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk update order status
     *
     * @param array $orderIds
     * @param string $status
     * @return int
     */
    public function bulkUpdateStatus(array $orderIds, $status)
    {
        return Order::whereIn('id', $orderIds)->update(['status' => $status]);
    }

    /**
     * Get orders by customer ID
     *
     * @param int $customerId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrdersByCustomerId($customerId)
    {
        return Order::where('customer_id', $customerId)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
