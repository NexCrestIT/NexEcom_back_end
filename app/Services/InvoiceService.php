<?php

namespace App\Services;

use App\Models\Order;
use DateTime;

class InvoiceService
{
    /**
     * Generate invoice data for an order
     *
     * @param Order $order
     * @return array
     */
    public static function generateInvoiceData(Order $order)
    {
        return [
            'invoice_number' => self::generateInvoiceNumber($order),
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'order_date' => $order->created_at->format('Y-m-d'),
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'customer' => [
                'name' => $order->customer->name ?? 'N/A',
                'email' => $order->customer->email ?? 'N/A',
                'phone' => $order->customer->phone_number ?? 'N/A',
            ],
            'billing_address' => [
                'name' => $order->address->first_name . ' ' . $order->address->last_name,
                'address' => $order->address->address_line_1,
                'address2' => $order->address->address_line_2,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'postal_code' => $order->address->postal_code,
                'country' => $order->address->country,
            ],
            'items' => $order->items->map(function($item) {
                return [
                    'name' => $item->product->name ?? 'Product',
                    'sku' => $item->product->sku ?? 'N/A',
                    'quantity' => $item->quantity,
                    'unit_price' => number_format($item->price, 2),
                    'total' => number_format($item->quantity * $item->price, 2),
                ];
            })->toArray(),
            'subtotal' => number_format($order->total_amount * 0.9, 2), // Assuming 10% tax for demo
            'tax' => number_format($order->total_amount * 0.1, 2),
            'total' => number_format($order->total_amount, 2),
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
            'notes' => $order->notes ?? '',
        ];
    }

    /**
     * Generate unique invoice number
     *
     * @param Order $order
     * @return string
     */
    public static function generateInvoiceNumber(Order $order)
    {
        return 'INV-' . $order->created_at->format('Y-m') . '-' . str_pad($order->id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Get invoice PDF (placeholder - would integrate with actual PDF library)
     *
     * @param Order $order
     * @return string
     */
    public static function generatePDF(Order $order)
    {
        $invoiceData = self::generateInvoiceData($order);
        
        // This is a placeholder. In production, you would use:
        // - Barryvdh\DomPDF or
        // - Spatie\Pdf or
        // - TCPDF or
        // - mPDF
        
        return json_encode($invoiceData);
    }

    /**
     * Send invoice to customer
     *
     * @param Order $order
     * @return bool
     */
    public static function sendInvoiceToCustomer(Order $order)
    {
        // Send invoice via email
        // $invoiceData = self::generateInvoiceData($order);
        // Mail::send('emails.invoice', $invoiceData, function($message) use ($order) {
        //     $message->to($order->customer->email)
        //             ->subject('Invoice - ' . $order->order_number);
        // });

        return true;
    }

    /**
     * Get invoice HTML for display
     *
     * @param Order $order
     * @return string
     */
    public static function getInvoiceHTML(Order $order)
    {
        $data = self::generateInvoiceData($order);
        
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px;">';
        $html .= '<h1>Invoice</h1>';
        $html .= '<p><strong>Invoice Number:</strong> ' . $data['invoice_number'] . '</p>';
        $html .= '<p><strong>Invoice Date:</strong> ' . $data['invoice_date'] . '</p>';
        $html .= '<p><strong>Order Number:</strong> ' . $data['order_number'] . '</p>';
        
        $html .= '<h2>Customer Information</h2>';
        $html .= '<p><strong>Name:</strong> ' . $data['customer']['name'] . '</p>';
        $html .= '<p><strong>Email:</strong> ' . $data['customer']['email'] . '</p>';
        $html .= '<p><strong>Phone:</strong> ' . $data['customer']['phone'] . '</p>';
        
        $html .= '<h2>Billing Address</h2>';
        $html .= '<p>' . $data['billing_address']['name'] . '<br>';
        $html .= $data['billing_address']['address'];
        if (!empty($data['billing_address']['address2'])) {
            $html .= '<br>' . $data['billing_address']['address2'];
        }
        $html .= '<br>' . $data['billing_address']['city'] . ', ' . $data['billing_address']['state'] . ' ' . $data['billing_address']['postal_code'];
        $html .= '<br>' . $data['billing_address']['country'] . '</p>';
        
        $html .= '<h2>Order Items</h2>';
        $html .= '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr style="background-color: #f5f5f5;">';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Product</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Quantity</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Unit Price</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';
        
        foreach ($data['items'] as $item) {
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . $item['name'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . $item['quantity'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">₹' . $item['unit_price'] . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">₹' . $item['total'] . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        
        $html .= '<div style="margin-top: 20px; text-align: right;">';
        $html .= '<p><strong>Subtotal:</strong> ₹' . $data['subtotal'] . '</p>';
        $html .= '<p><strong>Tax:</strong> ₹' . $data['tax'] . '</p>';
        $html .= '<p style="font-size: 1.2em;"><strong>Total:</strong> ₹' . $data['total'] . '</p>';
        $html .= '</div>';
        
        $html .= '<div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px;">';
        $html .= '<p><strong>Payment Method:</strong> ' . ucfirst($data['payment_method']) . '</p>';
        $html .= '<p><strong>Payment Status:</strong> ' . ucfirst($data['payment_status']) . '</p>';
        $html .= '<p><strong>Order Status:</strong> ' . ucfirst($data['order_status']) . '</p>';
        if (!empty($data['notes'])) {
            $html .= '<p><strong>Notes:</strong> ' . $data['notes'] . '</p>';
        }
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
}
