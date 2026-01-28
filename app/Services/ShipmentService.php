<?php

namespace App\Services;

use App\Models\Order;

class ShipmentService
{
    const CARRIER_FEDEX = 'fedex';
    const CARRIER_UPS = 'ups';
    const CARRIER_DHL = 'dhl';
    const CARRIER_USPS = 'usps';
    const CARRIER_INDIA_POST = 'india_post';
    const CARRIER_AMAZON = 'amazon';

    /**
     * Get available carriers
     *
     * @return array
     */
    public static function getCarriers()
    {
        return [
            self::CARRIER_FEDEX => 'FedEx',
            self::CARRIER_UPS => 'UPS',
            self::CARRIER_DHL => 'DHL',
            self::CARRIER_USPS => 'USPS',
            self::CARRIER_INDIA_POST => 'India Post',
            self::CARRIER_AMAZON => 'Amazon',
        ];
    }

    /**
     * Generate tracking number
     *
     * @param Order $order
     * @return string
     */
    public static function generateTrackingNumber(Order $order)
    {
        return 'TRK' . date('YmdHis') . str_pad($order->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Create shipment for order
     *
     * @param Order $order
     * @param array $data
     * @return array
     */
    public static function createShipment(Order $order, array $data)
    {
        $trackingNumber = self::generateTrackingNumber($order);

        return [
            'order_id' => $order->id,
            'tracking_number' => $trackingNumber,
            'carrier' => $data['carrier'] ?? self::CARRIER_FEDEX,
            'estimated_delivery' => $data['estimated_delivery'] ?? now()->addDays(5)->format('Y-m-d'),
            'shipped_date' => now()->format('Y-m-d'),
            'status' => 'in_transit',
            'shipment_items' => $order->items->map(function($item) {
                return [
                    'product_name' => $item->product->name ?? 'Product',
                    'quantity' => $item->quantity,
                    'weight' => $item->product->weight ?? 0,
                ];
            })->toArray(),
        ];
    }

    /**
     * Get tracking information
     *
     * @param string $trackingNumber
     * @return array
     */
    public static function getTrackingInfo($trackingNumber)
    {
        // This would integrate with actual carrier APIs
        return [
            'tracking_number' => $trackingNumber,
            'status' => 'in_transit',
            'current_location' => 'Distribution Center',
            'estimated_delivery' => now()->addDays(3)->format('Y-m-d'),
            'events' => [
                [
                    'date' => now()->format('Y-m-d H:i:s'),
                    'status' => 'Package in transit',
                    'location' => 'Distribution Center',
                ],
                [
                    'date' => now()->subDay()->format('Y-m-d H:i:s'),
                    'status' => 'Package picked up',
                    'location' => 'Warehouse',
                ],
            ],
        ];
    }

    /**
     * Update shipment status
     *
     * @param Order $order
     * @param string $status
     * @return bool
     */
    public static function updateShipmentStatus(Order $order, $status)
    {
        // Update order status based on shipment status
        $statusMap = [
            'in_transit' => 'shipped',
            'out_for_delivery' => 'shipped',
            'delivered' => 'delivered',
            'exception' => 'processing',
            'returned' => 'returned',
        ];

        if (isset($statusMap[$status])) {
            $order->update(['status' => $statusMap[$status]]);
            return true;
        }

        return false;
    }

    /**
     * Calculate shipping cost
     *
     * @param Order $order
     * @param string $carrier
     * @return float
     */
    public static function calculateShippingCost(Order $order, $carrier = self::CARRIER_FEDEX)
    {
        $baseRate = [
            self::CARRIER_FEDEX => 5.00,
            self::CARRIER_UPS => 4.50,
            self::CARRIER_DHL => 5.50,
            self::CARRIER_USPS => 3.00,
            self::CARRIER_INDIA_POST => 2.00,
            self::CARRIER_AMAZON => 3.50,
        ];

        $weight = $order->items->sum('product.weight') ?? 1;
        $distance = $order->address ? self::estimateDistance($order->address) : 1;

        return ($baseRate[$carrier] ?? 5.00) + ($weight * 0.5) + ($distance * 0.1);
    }

    /**
     * Estimate distance (placeholder)
     *
     * @param mixed $address
     * @return int
     */
    private static function estimateDistance($address)
    {
        return rand(100, 1000);
    }

    /**
     * Generate shipping label
     *
     * @param Order $order
     * @param string $carrier
     * @return array
     */
    public static function generateShippingLabel(Order $order, $carrier = self::CARRIER_FEDEX)
    {
        return [
            'order_number' => $order->order_number,
            'tracking_number' => self::generateTrackingNumber($order),
            'carrier' => $carrier,
            'from' => [
                'name' => 'NexEcom Store',
                'address' => 'Warehouse Address',
                'city' => 'Warehouse City',
                'state' => 'State',
                'postal_code' => '000000',
                'country' => 'Country',
            ],
            'to' => [
                'name' => $order->address->first_name . ' ' . $order->address->last_name,
                'address' => $order->address->address_line_1,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country,
            ],
            'items' => $order->items->map(function($item) {
                return [
                    'description' => $item->product->name ?? 'Product',
                    'quantity' => $item->quantity,
                    'weight' => $item->product->weight ?? 0,
                    'value' => $item->price * $item->quantity,
                ];
            })->toArray(),
            'total_weight' => $order->items->sum('product.weight') ?? 0,
            'total_value' => $order->total_amount,
        ];
    }

    /**
     * Bulk create shipments
     *
     * @param array $orderIds
     * @param array $shipmentData
     * @return int
     */
    public static function bulkCreateShipments(array $orderIds, array $shipmentData = [])
    {
        $count = 0;
        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            if ($order && OrderStatusService::canShip($order)) {
                self::createShipment($order, $shipmentData);
                $count++;
            }
        }
        return $count;
    }
}
