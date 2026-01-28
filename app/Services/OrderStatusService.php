<?php

namespace App\Services;

use App\Models\Order;

class OrderStatusService
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';

    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_COMPLETED = 'completed';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    /**
     * Get all available order statuses
     *
     * @return array
     */
    public static function getOrderStatuses()
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RETURNED => 'Returned',
        ];
    }

    /**
     * Get all available payment statuses
     *
     * @return array
     */
    public static function getPaymentStatuses()
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'Pending',
            self::PAYMENT_STATUS_COMPLETED => 'Completed',
            self::PAYMENT_STATUS_FAILED => 'Failed',
            self::PAYMENT_STATUS_REFUNDED => 'Refunded',
        ];
    }

    /**
     * Get status color (for UI)
     *
     * @param string $status
     * @return string
     */
    public static function getStatusColor($status)
    {
        $colors = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_PROCESSING => 'info',
            self::STATUS_SHIPPED => 'primary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_RETURNED => 'secondary',
        ];

        return $colors[$status] ?? 'secondary';
    }

    /**
     * Get payment status color (for UI)
     *
     * @param string $paymentStatus
     * @return string
     */
    public static function getPaymentStatusColor($paymentStatus)
    {
        $colors = [
            self::PAYMENT_STATUS_PENDING => 'warning',
            self::PAYMENT_STATUS_COMPLETED => 'success',
            self::PAYMENT_STATUS_FAILED => 'danger',
            self::PAYMENT_STATUS_REFUNDED => 'secondary',
        ];

        return $colors[$paymentStatus] ?? 'secondary';
    }

    /**
     * Check if status transition is valid
     *
     * @param string $currentStatus
     * @param string $newStatus
     * @return bool
     */
    public static function isValidTransition($currentStatus, $newStatus)
    {
        $validTransitions = [
            self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_SHIPPED => [self::STATUS_DELIVERED, self::STATUS_RETURNED],
            self::STATUS_DELIVERED => [self::STATUS_RETURNED],
            self::STATUS_CANCELLED => [],
            self::STATUS_RETURNED => [self::STATUS_PROCESSING],
        ];

        return in_array($newStatus, $validTransitions[$currentStatus] ?? []);
    }

    /**
     * Get next possible statuses for a given status
     *
     * @param string $currentStatus
     * @return array
     */
    public static function getNextPossibleStatuses($currentStatus)
    {
        $possibleStatuses = [
            self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
            self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
            self::STATUS_SHIPPED => [self::STATUS_DELIVERED, self::STATUS_RETURNED],
            self::STATUS_DELIVERED => [self::STATUS_RETURNED],
            self::STATUS_CANCELLED => [],
            self::STATUS_RETURNED => [self::STATUS_PROCESSING],
        ];

        $nextStatuses = [];
        foreach ($possibleStatuses[$currentStatus] ?? [] as $status) {
            $statuses = self::getOrderStatuses();
            $nextStatuses[$status] = $statuses[$status];
        }

        return $nextStatuses;
    }

    /**
     * Process refund for an order
     *
     * @param Order $order
     * @param float|null $amount
     * @return bool
     */
    public static function processRefund(Order $order, $amount = null)
    {
        $refundAmount = $amount ?? $order->total_amount;

        // Update order payment status
        $order->update([
            'payment_status' => self::PAYMENT_STATUS_REFUNDED,
        ]);

        // Here you would integrate with payment gateway (Razorpay, etc.)
        // For now, we'll just update the status

        return true;
    }

    /**
     * Can refund order
     *
     * @param Order $order
     * @return bool
     */
    public static function canRefund(Order $order)
    {
        return in_array($order->payment_status, [
            self::PAYMENT_STATUS_COMPLETED,
        ]);
    }

    /**
     * Can cancel order
     *
     * @param Order $order
     * @return bool
     */
    public static function canCancel(Order $order)
    {
        return in_array($order->status, [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ]);
    }

    /**
     * Can ship order
     *
     * @param Order $order
     * @return bool
     */
    public static function canShip(Order $order)
    {
        return $order->status === self::STATUS_PROCESSING &&
               $order->payment_status === self::PAYMENT_STATUS_COMPLETED;
    }
}
